<?php
declare(strict_types=1);
namespace App\Controllers\Admin;

use Framework\Controller;
use Framework\Helpers\CSRF;
use Framework\Helpers\CSV;
use Framework\Helpers\Session;
use Framework\Helpers\Auth;
use Framework\Helpers\Media;
use Framework\Response;
use App\Models\Admin;
use App\Models\User;

class Admins extends Controller
{

    protected int $page = 1;
    protected int $limit = 1;

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
            'alert' => Session::flash(['warning', 'danger', 'success', 'info']),
        ]);
    }

    protected function offset(): int
    {
        return $this->page * $this->limit - $this->limit;
    }

    public function allUsers(): Response
    {
        $offset = $this->offset();
        $users = $this->userModel->pullAllByLimit($this->limit, $offset);
        $i = $offset + 1;
        $count = $this->adminModel->rowTotal('user');
        $total_pages = ceil($count / $this->limit);
        if($this->page > $total_pages){
            return $this->redirect("/admin/users/page/{$total_pages}"); 
        }
        return $this->view('admins/all-users', [
            'users' => $users,
            'count' => $count,
            'role' => 'users',
            'page_url' => $_ENV['URL_ROOT'] ."/admin/users",
            'current_page' => $this->page,
            'total_pages' => $total_pages,
            'i' => $i,
        ]);
    }

    public function allUsersPage($page = 1): Response
    {
        $this->page = (int) $page;
        return $this->allUsers();
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
        $offset = $this->offset();
        $i = $offset + 1;
        $papers = $this->adminModel->allPapersByLimit( $this->limit, $offset);
        $count = $this->adminModel->rowTotal('paper');
        $total_pages = ceil($count / $this->limit);
        if($this->page > $total_pages){
            return $this->redirect("/admin/papers/page/{$total_pages}"); 
        }
        return $this->view('admins/all-papers', [
            'papers' => $papers,
            'count' => $count,
            'current_page' => $this->page,
            'page_url' => $_ENV['URL_ROOT'] ."/admin/papers",
            'total_pages' => $total_pages,
            'i'=> $i,
        ]);
    }

    public function allPapersPage($page = 1): Response
    {
        $this->page = (int) $page;
        return $this->allPapers();
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
        CSRF::check($this->request->post['csrf_token']);
        $paper = $this->adminModel->authPaper($code);
        $user = $this->adminModel->getById($paper->user_id, 'user');
        $post = [
            'status' => $this->request->post['status'],
            'delete' => $this->request->post['delete'],
        ];
        $this->adminModel->updatePaperById($paper->id, $post);
        $settings['view_result'] =  isset($this->request->post['view_result']) ? 1 : 0;
        $settings['view_answers'] =  isset($this->request->post['view_answers']) ? 1 : 0;
        $settings['view_result'] = !empty($settings['view_answers']) ? 1 : $settings['view_result'];
        $data = [
            'name' => $this->request->post['name'],
            'settings' => json_encode($settings),
        ];
        $this->adminModel->updateRowById($paper->id, $data, 'paper' );
        $paper = $this->adminModel->authPaper($code);
        Session::set('success', 'Paper settings has been updated');
        return $this->view('admins/edit-paper', [
            'paper' => $paper,
            'user' => $user,
            'alert' => Session::flash(['success', 'danger', 'warning']),
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
        $offset = $this->offset();
        $users = $this->adminModel->pullAllByFieldAndLimit('role', 'instructor', $this->limit, $offset, 'user');
        $i = $offset + 1;
        $count = $this->adminModel->rowTotalByField('role','instructor', 'user');
        $total_pages = ceil($count / $this->limit);
        if($this->page > $total_pages){
            return $this->redirect("/admin/instructors/page/{$total_pages}"); 
        }
        return $this->view('admins/all-users', [
            'users' => $users,
            'count' => $count,
            'role' => 'instructors',
            'page_url' => $_ENV['URL_ROOT'] ."/admin/instructors",
            'current_page' => $this->page,
            'total_pages' => $total_pages,
            'i' => $i
        ]);
    }

    public function allInstructorsPage($page = 1): Response
    {
        $this->page = (int) $page;
        return $this->allInstructors();
    }

    public function allStudents(): Response
    {
        $offset = $this->offset();
        $users = $this->adminModel->pullAllByFieldAndLimit('role', 'student', $this->limit, $offset, 'user');
        $i = $offset + 1;
        $count = $this->adminModel->rowTotalByField('role','instructor', 'user');
        $total_pages = ceil($count / $this->limit);
        if($this->page > $total_pages){
            return $this->redirect("/admin/students/page/{$total_pages}"); 
        }
        return $this->view('admins/all-users', [
            'users' => $users,
            'count' => $count,
            'role' => 'students', 
            'page_url' => $_ENV['URL_ROOT'] ."/admin/students",
            'current_page' => $this->page,
            'total_pages' => $total_pages,
            'i' => $i
        ]);
    }

    public function allStudentsPage($page = 1): Response
    {
        $this->page = (int) $page;
        return $this->allStudents();
    }

    public function allAdmins(): Response
    {
        $offset = $this->offset();
        $users = $this->adminModel->pullAllByFieldAndLimit('role', 'admin', $this->limit, $offset, 'user');
        $i = $offset + 1;
        $count = $this->adminModel->rowTotalByField('role','admin', 'user');
        $total_pages = ceil($count / $this->limit);
        if($this->page > $total_pages){
            return $this->redirect("/admin/admins/page/{$total_pages}"); 
        }
        return $this->view('admins/all-users', [
            'users' => $users,
            'count' => $count,
            'role' => 'admins',
            'page_url' => $_ENV['URL_ROOT'] ."/admin/admins",
            'current_page' => $this->page,
            'total_pages' => $total_pages,
            'i' => $i
        ]);
    }

    public function allAdminsPage($page = 1): Response
    {
        $this->page = (int) $page;
        return $this->allAdmins();
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
            'CSRF' => CSRF::generate(),
            'alert' => Session::flash(['success', 'danger', 'warning']),
            'csv' => $csv,
        ]);
    }

    public function banQuestion($code, $id): Response
    {
        CSRF::check($this->request->post['csrf_token']);
        if(file_exists("{$_ENV['CSV_PATH']}/papers/{$code}.csv")){
            $csv = new CSV($code, 'papers');
            $csv->updateRows(['ban'=>1], $id);
            Session::set('warning', 'question Banned');
        }else{
            Session::set('warning', 'Paper does not exist');
        }
        return $this->redirect("admin/paper/{$code}/questions-list");
    }

    public function unbanQuestion($code, $id): Response
    {
        CSRF::check($this->request->post['csrf_token']);
        if(file_exists("{$_ENV['CSV_PATH']}/papers/{$code}.csv")){
            $csv = new CSV($code, 'papers');
            $csv->updateRows(['ban'=>0], $id);
            Session::set('success', 'question Unbanned');
        }else{
            Session::set('warning', 'Paper does not exist');
        }
        return $this->redirect("admin/paper/{$code}/questions-list");
    }

    public function viewQuestion($code, $id): Response
    {
        $paper = $this->adminModel->pullByField('code', $code, 'paper');
        $csv = new CSV($code, 'papers');
        $question = $csv->getRow($id);
        $instructor = $this->adminModel->pullById($paper->user_id, 'user');
        return $this->view('admins/view-question', [
            'paper' => $paper,
            'instructor' => $instructor,
            'question' => $question,
            'answers' => json_decode($question->answers),
            'image' => $question->image ? Media::questionImage( $question->image ) : null,
        ]);
    }
}