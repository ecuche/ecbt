<?php
declare(strict_types=1);
namespace App\Controllers\Admin;

use Framework\Controller;
use Framework\Helpers\CSRF;
use Framework\Helpers\CSV;
use Framework\Helpers\Session;
use Framework\Helpers\Auth;
use Framework\Response;
use App\Models\Admin;
use App\Models\User;

class Admins extends Controller
{
    public function __construct(private Admin $adminModel, private User $userModel)
    {
        Auth::failRedirect('','You need to be logged in to access this page');
        if(Session::get('role') !== 'admin'){
            Session::set('warning', 'You are not allowed to view this Page');
            $this->redirect('');
        } 
    }

    public function dashboard(): Response
    {
        return $this->view('admins/dashboard', [
        ]);
    }

    public function allUsers(): Response
    {
        $users = $this->userModel->pullAllByLimit();
        $count = $this->adminModel->rowTotal('user');
        return $this->view('admins/all-users', [
            'users' => $users,
            'count' => $count,
            'offset' => 1
        ]);
    }

    public function findUser(): Response
    {
        return $this->view('admins/find-user', [
            'CSRF'=> CSRF::generate(),
        ]);
    }

    public function getUser(): Response
    {
        CSRF::check($this->request->post['csrf_token']);
        $post = [
            'email' => $this->request->post['email'],
        ];
        $this->adminModel->validateFindUser($post);
        if(empty($this->adminModel->getErrors())){
            return $this->redirect("admin/edit/user/{$post['email']}");
        }
        return $this->view('admins/find-user', [
            'CSRF'=> CSRF::generate(),
            'errors'=> (object) $this->adminModel->getErrors(),
        ]);
    }

    public function editUser($email): Response
    {
        $user = $this->adminModel->authUserEmail($email);
        return $this->view('admins/edit-user', [
            'user' => $user,
            'CSRF' => CSRF::generate(),
            'alert' => Session::flash(['warning', 'success', 'danger'])
        ]);
    }

    public function updateUser($email): Response
    {
        $user = $this->adminModel->authUserEmail($email);
        $post =  [
            'delete' => $this->request->post['delete'],
            'role' => $this->request->post['role'],
            'active'=> $this->request->post['active'],
        ];
        $this->adminModel->updateUserById($user->id, $post);
        Session::set('success', 'User has been Updated');
        return $this->redirect("admin/edit/user/{$user->email}");

    }

    public function findPaper(): Response
    {
        return $this->view('admins/find-paper', [
            'CSRF' => CSRF::generate()
        ]);
    }

    public function getPaper(): Response
    {
        CSRF::check($this->request->post['csrf_token']);
        $code = $this->request->post['code'];
        $paper = $this->adminModel->authPaper($code);
        return  $this->redirect("admin/edit/paper/{$paper->code}");
    }

    public function allPapers(): Response
    {
        $papers = $this->adminModel->allPapersByLimit();
        $count = $this->adminModel->rowTotal('user');
        return $this->view('admins/all-papers', [
            'papers' => $papers,
            'count' => $count
        ]);
    }

    public function editPaper($code): Response
    {
        $paper = $this->adminModel->authPaper($code);
        $user = $this->adminModel->findById($paper->user_id, 'user');
        return $this->view('admins/edit-paper', [
            'paper' => $paper,
            'user' => $user,
            'CSRF' => CSRF::generate(),
            'settings' => json_decode($paper->settings)
        ]);
    }

    public function updatePaper($code): Response
    {

        var_dump($_POST);
        exit;
        $paper = $this->adminModel->authPaper($code);
        $user = $this->adminModel->findById($paper->user_id, 'user');
        return $this->view('admins/edit-paper', [
            'paper' => $paper,
            'user' => $user,
            'CSRF' => CSRF::generate(),
            'settings' => json_decode($paper->settings)
        ]);
    }

    public function instructorPapers($email): Response
    {
        $user = $this->adminModel->findByFields(['email'=>$email, 'role'=>'instructor'], 'user');
        $papers = $this->adminModel->findAllByField('user_id', $user->id, 'paper');
        return $this->view('admins/instructor-papers', [
            'papers' => $papers,
            'user' => $user,
        ]);
    }

    public function allInstructors(): Response
    {
        $users = $this->adminModel->pullAllByField('role', 'instructor', 'user');
        $count = (array) $users;
        return $this->view('admins/all-instructors', [
            'users' => $users,
            'count' => $count,
            'offset' => 1
        ]);
    }

    public function allStudents(): Response
    {
        $users = $this->adminModel->pullAllByField('role', 'student', 'user');
        $count = (array) $users;
        return $this->view('admins/all-instructors', [
            'users' => $users,
            'count' => $count,
            'offset' => 1
        ]);
    }

    public function allAdmins(): Response
    {
        $users = $this->adminModel->pullAllByField('role', 'admin', 'user');
        $count = (array) $users;
        return $this->view('admins/all-instructors', [
            'users' => $users,
            'count' => $count,
            'offset' => 1
        ]);
    }

    public function questionsList($code): Response
    {
        $paper = $this->adminModel->pullByField('code', $code, 'paper');
        $user = $this->adminModel->pullById($paper->user_id, 'user');
        $csv = new CSV($paper->code, 'papers');
        $csv = $csv->getRow();
        return $this->view('admins/questions-list', [
            'paper' => $paper,
            'user' => $user,
            'csv' => $csv,
        ]);
    }
}