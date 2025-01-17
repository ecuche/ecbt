<?php
declare(strict_types=1);
namespace App\Controllers;
use Framework\Controller;
use Framework\Helpers\Redirect;
use Framework\Response;
use App\Models\User;
use Framework\Helpers\Session;
use Framework\Exceptions\PageNotFoundException;
use Framework\Helpers\Auth;
use Framework\Helpers\CSRF;
use Framework\Helpers\Mail;
use Framework\Helpers\Token;



class Homes extends Controller
{
    public function __construct(private User $usersModel)
    {
        if(!Auth::isLoggedIn()){
            if($usersModel->loginFromRemeberCookie()){
                Session::set("success", 'Welcome back');
                Redirect::to("/dashboard");
            }
        }
    }

    public function index(): Response
    {
        $role = $_SESSION['role'] ?? '';
        Auth::passRedirect("/{$role}/dashboard", '');
        return $this->view('homes/index', [
            'alert' => Session::flash(['warning', 'danger', 'success']),
            'CSRF'=>CSRF::generate()
        ]);
    }

    public function register(): Response
    {
        return $this->view('homes/register', [
            'CSRF'=>CSRF::generate()
        ]);
    }

    public function registerNewUser(): Response
    {
        CSRF::check($this->request->post['csrf_token']);
        $data = [
            'name' => ucwords($this->request->post['name']),	
            'email' => $this->request->post['email'],
            'password' => $this->request->post['password'],
        ];
        $data = (object)$data;
        $this->usersModel->validateRegistration($data);

        if(empty($this->usersModel->getErrors())){
            $data->password = password_hash($data->password, PASSWORD_DEFAULT); 
            if($this->usersModel->insert($data)){
                $user = $this->usersModel->findByField('email', $data->email);
                $this->usersModel->sendActivation($user);
                Session::set('success','Registration successful: Check your email for verification');
                return $this->redirect("");
            }else{
                throw new PageNotFoundException("could not register user");
            }
        }else{
            unset($data->password);
            return $this->view('homes/register', [
                'errors'=> (object) $this->usersModel->getErrors(),
                'user' => $data,
                'CSRF'=> CSRF::generate()
            ]);
        }
    }

    public function logInUser(): Response
    {
        CSRF::check($this->request->post['csrf_token']);
        $data = [
            'email' => $this->request->post['email'],
            'password' => $this->request->post['password'],
            'remember_me' => isset($this->request->post['remember_me']),
        ];
        $data = (object)$data;
        $this->usersModel->validateLogIn($data);
        if(empty($this->usersModel->getErrors())){
            $user = $this->usersModel->loginUser($data); 
            if(!empty($user)){
                if($user->active === 0){
                    Session::set('warning','Account not activated. Kindly check your mail for activation');
                    return $this->redirect("");
                }else{
                    Auth::login($user);
                    $user->remember_me = $data->remember_me;
                    $this->usersModel->rememberLogin($user);
                    $page = Auth::returnPage();
                    if(!empty($page)){
                        return $this->redirect($page);
                    }else{
                        Session::set('success','Login successful');
                        return $this->redirect("{$user->role}/dashboard");
                    }
                }
            }else{
                throw new PageNotFoundException("could not login user");
            }
        }else{
            unset($data->password);
            $errors = (object) $this->usersModel->getErrors();
            return $this->view('homes/index', [
                'errors'=> $errors,
                'danger'=> $errors->login ?? null,
                'user' => $data,
                'CSRF'=> CSRF::generate()
            ]);
        }
    }

    public function logOutUser(): Response
    {
        $this->usersModel->destroyByfield('user_id', Session::get('id'), 'remembered_logins');
        Auth::logout();
        Session::set(['success'=>'You have logged out Successfully']);
        return $this->redirect("");
    }

    public function forgotPassword(): Response
    {
        return $this->view('homes/forgot-password', [
            'CSRF'=>CSRF::generate()
        ]);
    }

    public function recoverAccount(): Response
    {
        CSRF::check($this->request->post['csrf_token']);
        $data = [
            'email' => $this->request->post['email'],
        ];
        $data = (object)$data;
        $this->usersModel->validateRecoverAccount($data->email);
        if(empty($this->usersModel->getErrors())){
            $this->usersModel->resetAccount($data->email);
            Session::set('success','Kindly check your mail to recover your account if your are registered');
            return $this->redirect("");
        }else{
            return $this->view('homes/forgot-password', [
                'errors'=> (object) $this->usersModel->getErrors(),
                'user' => $data,
                'CSRF'=>CSRF::generate()
            ]);
        }
    }

    public function resetPassword($email, $hash): Response
    {
        $user = $this->usersModel->findByField('email', $email);
        $hash_row = $this->usersModel->findByField('user_id', $user->id, 'password_reset');
        if(!empty($hash_row) && ($hash_row->hash === $hash)){
            if (strtotime($hash_row->expiry) > time()) {
                return $this->view('homes/reset-password', [
                    'user' => $user,
                    'hash'=> $hash_row,
                    'CSRF'=>CSRF::generate()
                ]);
            }
            Session::set('warning','password reset has expired');
            return $this->redirect("");
        }else{
            return $this->redirect("500");
        }   
    }

    public function passwordReset($email, $hash): Response
    {
        CSRF::check($this->request->post['csrf_token']);
        $user = $this->usersModel->findByField('email', $email);
        $hash_row = $this->usersModel->findByField('user_id', $user->id,  'password_reset');
        $data = [
            'password' => $this->request->post['password'],
            'password_again' => $this->request->post['password_again'],
        ];
        $data = (object)$data;
        $this->usersModel->validatePasswordReset($data);
        if(empty($this->usersModel->getErrors())){
            if(!empty($user)){
                $new_password = password_hash($data->password, PASSWORD_DEFAULT); 
                $this->usersModel->updateRow($user->id, ['password' => $new_password]);
                $this->usersModel->destroyRow($hash_row->id, 'password_reset');
                $mail = new Mail;
                $mail->to($user->email, $user->name);
                $mail->subject('Password Reset Successful');
                $mail->is_html();
                $mail->message("Your password has been reset successfully. If you did not request this, kindly contact us immediately");
                $mail->send();
                Session::set('success','Password reset successful. Kindly login with your new password');
                return $this->redirect('');
            }else{
                throw new PageNotFoundException("Password Reset was not Successfull");
            }
        }else{
            return $this->view('homes/reset-password', [
                'errors'=> (object) $this->usersModel->getErrors(),
                'user' => $user,
                'hash'=> $hash_row,
                'CSRF'=> CSRF::generate()
            ]);
        }
    }

    public function activateAccount($email, $hash): Response
    {
        $user = $this->usersModel->findByField('email', $email);
        if(!empty($user)){
            if($user->active === 0){
                $sign = $user->updated_on ?? $user->created_on;
                $token = new Token($sign);
                $token = $token->getHash();
                if($token === $hash){
                    if($this->usersModel->updateRow($user->id, ['active'=>1])){
                        Session::set('success','Account activated successfully. Kindly login');
                        return $this->redirect('');
                    }
                }
                return $this->redirect("500");
            }
            Session::set('success','Account is active. Kindly login');
            return $this->redirect("");
        }else{
            return $this->redirect("500");
        }
    }

    public function aboutUs(): Response
    {
        return $this->view('homes/about-us', []);
    }

    public function contact(): Response
    {
        return $this->view('homes/contact-us', [
            'CSRF'=> CSRF::generate()
        ]);
    }

    public function contactUs(): Response
    {
        CSRF::check($this->request->post['csrf_token']);
        $data = [
            'email' => $this->request->post['email'],
            'name' => $this->request->post['name'],
            'message' => $this->request->post['message'],
            'subject' => $this->request->post['subject'],
        ];
        $data = (object)$data;
        
        $this->usersModel->validateContactUs($data);
        if(empty($this->usersModel->getErrors())){
            $mail = new Mail;
            $mail->to('contact@LogReg.com');
            $mail->from($data->email, $data->name);
            $mail->replyto($data->email, $data->name);
            $mail->subject($data->subject);
            $mail->message($data->message);
            $mail->is_html();
            if($mail->send()){
                Session::set('success','Message sent. Thanks for contacting us');
                return $this->redirect("");
            }else{
                return $this->redirect("505");
            }
        }else{
            return $this->view('homes/contact-us', [
                'errors'=> (object) $this->usersModel->getErrors(),
                'contact' => $data,
                'CSRF'=> CSRF::generate()
            ]);
        }
    }

    public function e404(): Response
    {
        return $this->view('404', []);
    }

    public function e500(): Response
    {
        return $this->view('500', []);
    }

    public function test()  
    {


        //always leave this exit line if you will not use template
        exit;
    }


}