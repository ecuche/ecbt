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
use App\Models\Student;
use App\Models\Home;

class Homes extends Controller
{
    public function __construct(private User $usersModel, private Student $studentModel, private Home $homeModel)
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
        ]);
    }

    public function logInUser(): Response
    {
        $data = [
            'email' => $this->request->post['email'],
            'password' => $this->request->post['password'],
            'remember_me' => isset($this->request->post['remember_me']),
        ];
        $data = (object)$data;
        $this->usersModel->validateLogIn($data);
        if(empty($this->usersModel->getErrors())){
            $user = $this->usersModel->loginUser($data);
            $user->role = $this->homeModel->getByField('id',$user->role_id,'role')->name;
            if(!empty($user)){
                if($user->active === 0){
                    Session::set('warning','Account not activated. Kindly check your mail for activation');
                    return $this->redirect("");
                }else{
                    Auth::login($user);
                    $user->remember_me = $data->remember_me;
                    $this->usersModel->rememberLogin($user);
                    $page = !empty($_SESSION['current_url']) ? $_SESSION['current_url'] : Auth::returnPage();
                    Session::delete('current_url');
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
            Session::set("danger", $errors->login ?? null);
            return $this->view('homes/index', [
                'errors'=> $errors,
                'user' => $data,
            ]);
        }
    }

    public function register(): Response
    {
        return $this->view('homes/register', [
        ]);
    }

    public function registerNewUser(): Response
    {
        $data = [
            'name' => ucwords($this->request->post['name']),	
            'email' => $this->request->post['email'],
            'password' => $this->request->post['password'],
        ];
        $data = (object)$data;
        $this->usersModel->validateRegistration($data);

        if(empty($this->usersModel->getErrors())){
            $data->password = password_hash($data->password, PASSWORD_DEFAULT);
            $data->role_id = 3; 
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
        ]);
    }

    public function recoverAccount(): Response
    {
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
                    'hash'=> $hash_row
                ]);
            }
            Session::set('warning','password reset has expired');
            return $this->redirect("");
        }else{
            throw new PageNotFoundException("could not reset password");
        }   
    }

    public function passwordReset($email, $hash): Response
    {
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
                $this->usersModel->updateRowById($user->id, ['password' => $new_password]);
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
                    if($this->usersModel->updateRowById($user->id, ['active'=>1])){
                        Session::set('success','Account activated successfully. Kindly login');
                        return $this->redirect('');
                    }
                }
                Session::set('danger','wrong token');
                return $this->redirect("");
            }
            Session::set('success','Account is active. Kindly login');
            return $this->redirect("");
        }else{
            Session::set('warning','user does not exist');
            return $this->redirect("");
        }
    }

    public function findTest(): Response
    {
        return $this->view('homes/find-test', [
        ]);
    }

    public function searchResult($code): Response
    {
        
        $paper = $this->usersModel->findByField('code', $code, 'paper');
        if(Auth::isLoggedIn()){
            if(!empty(Session::get('test_name'))) {
                $this->redirect("paper/{$code}/test/sheet");
            }
            $result = $this->usersModel->findByFields(['user_id' => $_SESSION['id'], 'paper_id' => $paper->id], 'result');
            $rem_time  = $this->studentModel->checkResultTime($paper);
        }
        $instructor = $this->usersModel->findById($paper->user_id, 'user');
        $current_url = substr($_SERVER['REQUEST_URI'], strlen($_ENV["SITE_DIR"]));
        if(!Auth::isLoggedIn()){ Session::set('current_url', $current_url);}
        return $this->view('homes/search-result', [
            'paper' => $paper,
            'instructor' => $instructor,
            'result' => $result ?? null,
            'rem_time' => $rem_time ?? false,
        ]);
    }

    public function searchTest(): Response
    {
        $post = (object) ['code' => $this->request->post['code']];
        $post->code = strtoupper($post->code);
        $errors = $this->usersModel->validateCode($post);
        $paper = $this->usersModel->findByField('code', $post->code, 'paper');
        if (empty((array) $errors)) {
            return $this->redirect("test/{$paper->code}/search/result");
        } else {
            return $this->view('homes/find-test', [
                'paper' => $post->code,
                'errors' => $errors
            ]);
        }
    }


    public function aboutUs(): Response
    {
        return $this->view('homes/about-us', []);
    }

    public function contact(): Response
    {
        return $this->view('homes/contact-us', [
        ]);
    }

    public function contactUs(): Response
    {
        $data = [
            'email' => $this->request->post['email'],
            'name' => $this->request->post['name'],
            'phone' => $this->request->post['phone'],
            'message' => $this->request->post['message'],
            'subject' => $this->request->post['subject'],
        ];
        $data = (object)$data;
        
        $this->usersModel->validateContactUs($data);
        if(empty($this->usersModel->getErrors())){
            $this->usersModel->insert($data, 'contact_us');
            $mail = new Mail;
            $mail->to($_ENV['CONTACTUS_EMAIL']);
            $mail->from($data->email, $data->name);
            $mail->replyto($data->email, $data->name);
            $mail->subject($data->subject);
            $mail->message($data->message);
            $mail->is_html();
            if($mail->send()){
                Session::set('success','Message sent. Thanks for contacting us');
                return $this->redirect("");
            }else{
                Session::set('warning','Message Failed. Try again');
                return $this->redirect("contact");
            }
        }else{
            return $this->view('homes/contact-us', [
                'errors'=> (object) $this->usersModel->getErrors(),
                'contact' => $data,
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

    public function test(): Response  
    {

        $url = substr($_SERVER['REQUEST_URI'], 1);;
        echo $url;
        // return $this->view('homes/test', [
        //     'page'=>'date_format'
        // ]);
        // always leave this exit line if you will not use template
        exit;
    }
}