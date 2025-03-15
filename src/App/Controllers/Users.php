<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Models\User;
use Framework\Controller;
use Framework\Response;
use Framework\Helpers\Auth;
use Framework\Helpers\Session;
use Framework\Helpers\CSRF;
use Framework\Helpers\Data;
use Framework\Helpers\Mail;
use Framework\Exceptions\PageNotFoundException;

class Users extends Controller
{
    private $user;

    public function __construct(private User $usersModel)
    {
        $this->user = $this->usersModel->getUser() ?? false;
    }

    public function dashboard(): Response         
    { 
        Auth::failRedirect("", "Kindly login to access your dashboard");
        return $this->view('users/dashboard', [
            'user' => $this->user,
        ]);
    }
   
    public function updateProfile(): Response
    {
        $user = $this->user;
        return $this->view('users/update-profile', [
            'user' => (object) $user,
            'CSRF' => CSRF::generate()
        ]);
    }

    public function profileUpdate(): Response
    {
        CSRF::check($this->request->post['csrf_token']);
        $user = $this->user;
        $data = [
            'id' => $user->id,
            'name' => $this->request->post['name'],
            'reg_no' => $this->request->post['reg_no'],
            'email' => $this->request->post['email'],
            'phone' => $this->request->post['phone']
        ];
        $data = (object)$data;
        $this->usersModel->validateProfileUpdate($data);
        if(empty($this->usersModel->getErrors())){
            if(!empty($user)){
                $this->usersModel->updateRowById($user->id, ['name' => $data->name, 'reg_no'=> $data->reg_no, 'phone'=> $data->phone]);
                if($user->email !== $data->email){
                    $this->usersModel->updateRowById($user->id, ['email' => $data->email, 'active'=>0]);
                    $this->usersModel->destroyByfield('user_id', $user->id, 'remembered_logins');
                    $user = $this->usersModel->findById($user->id);
                    $this->usersModel->sendActivation($user);
                    Auth::logout('Email reset successful. Kindly check your email to activate your account');
                    return $this->redirect('');
                }
                Session::set('success','Your Profile has been updated');
                return $this->redirect('profile/view');
            }else{
                throw new PageNotFoundException("Password Reset was not Successfull");
            }
        }else{
            $user->name = $data->name;
            $user->email = $data->email;
            return $this->view('users/update-profile', [
                'errors'=> (object) $this->usersModel->getErrors(),
                'user' => $user,
                'CSRF'=> CSRF::generate()
            ]);
        }
    }

    public function viewProfile(): Response
    {
        $user = $this->user;
        return $this->view('users/view-profile', [
            'user' => (object) $user,
            'time_ago' => Data::timeAgo($user->created_on)
        ]);
    }

    public function  viewProfileByEmail(string $email): Response
    {
        $user = $this->usersModel->getByField('email', $email);
        return $this->view('users/view-public-profile', [
            'user' => (object) $user,
            'time_ago' => Data::timeAgo($user->created_on)
        ]);
    }

    public function passwordUpdate(): Response
    {
        $user = $this->user;
        return $this->view('users/change-password', [
            'user' => (object) $user,
            'CSRF' => CSRF::generate()
        ]);
    }

    public function updatePassword(): Response
    {
        CSRF::check($this->request->post['csrf_token']);
        $user = $this->user;
        $data = [
            'old_password' => $this->request->post['old_password'],
            'new_password' => $this->request->post['new_password'],
            'password_again' => $this->request->post['password_again'],
        ];
        $data = (object)$data;
        $this->usersModel->validatePasswordUpdate($data, $user);
        if(empty($this->usersModel->getErrors())){
            if(!empty($user)){
                $new_password = password_hash($data->new_password, PASSWORD_DEFAULT); 
                $this->usersModel->updateRowById($user->id, ['password' => $new_password]);
                $mail = new Mail;
                $mail->to($user->email, $user->name);
                $mail->subject('Password Reset Successful');
                $mail->message("Your password has been reset successfully. If you did not request this, kindly contact us immediately");
                $mail->is_html();
                $mail->send();
                Session::set('success','Password reset successful. Kindly login with your new password');
                return $this->redirect('dashboard');
            }else{
                throw new PageNotFoundException("Password Reset was not Successfull");
            }
        }else{
            return $this->view('users/change-password', [
                'errors'=> (object) $this->usersModel->getErrors(),
                'user' => $user,
                'CSRF'=> CSRF::generate()
            ]);
        }
    }

    public function edit(string $id): Response
    {
        $id = (int)$id;
        $user = $this->usersModel->findById($id);
       
        return $this->view('users/edit', [
            "user" => $user
        ]);
    }

    public function update(string $id): Response
    {
        $id = (int)$id;
        $user = $this->usersModel->findById($id);
        $user->name = $this->request->post['name'];
        $update = $this->request->post;
        

        if($this->usersModel->updateRowById($id, $update)){
            header("Location: {$_ENV['URL_ROOT']}/users/show/{$id}");
            exit;
        }else{
            return $this->view('users/edit', [
                'user' => $user,
                'errors' => $this->usersModel->getErrors()
            ]);
        }
    }

    
    public function destroy(string $id): Response
    {
        $id = (int)$id;
        $this->usersModel->findById( $id);
        $this->usersModel->deleteRow($id);
        header("Location: {$_ENV['URL_ROOT']}/users/index");
        exit;
    }

    public function delete(string $id): Response
    {
        $id = (int)$id;
        $user = $this->usersModel->findById($id);
        return $this->view('users/delete', [
            "user" => $user
        ]);
    }

    public function responseCodeExample(): Response
    {
        $this->response->setStatusCode(451);
        $this->response->setBody("Unavailable for legal reasons");
        return $this->response;
    }
}