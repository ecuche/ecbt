<?php
declare(strict_types=1);
namespace App\Models;

use Framework\Model;
use Framework\Helpers\Session;
use Framework\Helpers\Redirect;

class Admin extends Model
{

    public function authUserEmail($email)
    {
        $user = $this->pullByField('email', $email, 'user');
        if (empty($user)) {
            Session::set('warning','User does not exist');
            Redirect::to('/dashboard');
        }else{
            return $user;
        }
    }

    public function authPaper($code)
    {
        $paper = $this->pullByField('code', $code, 'paper');
        if (empty($paper)) {
            Session::set('warning','Paper does not exist');
            Redirect::to('/dashboard');
        }else{
            return $paper;
        }
    }

    public function updateUserById($id, $data)
    {
        if($data['delete'] === 'delete'){
            $this->amendRowById($id, ['deleted_on'=> date('Y-m-d H:i:s')], 'user');
        }elseif($data['delete'] === 'recover'){
            $this->amendRowById($id, ['deleted_on'=> NULL], 'user');
        }
        if($data['role'] !== ''){
            $this->amendRowById($id, ['role'=> $data['role']], 'user');
        }
        if($data['active'] !== ''){
            $this->amendRowById($id, ['active'=> $data['active']], 'user');
        }
    }

    public function updatePaperById($id, $data)
    {
        if($data['delete'] === 'delete'){
            $this->amendRowById($id, ['deleted_on'=> date('Y-m-d H:i:s')], 'paper');
        }elseif($data['delete'] === 'recover'){
            $this->amendRowById($id, ['deleted_on'=> NULL], 'paper');
        }
       
        if($data['status'] !== ''){
            $this->amendRowById($id, ['status'=> $data['status']], 'paper');
        }
    }

    public function allPapersByLimit(int $limit = 10, int $offset = 0): array|object
    {
        $sql = "SELECT *,
        paper.id as paperId,
        paper.name as paperName,
        paper.created_on as paperCreatedOn,
        paper.updated_on as paperUpdatedOn,
        paper.deleted_on as paperDeletedOn,
        user.id as userId,
        user.name as userName,
        user.created_on as userCreatedOn,
        user.updated_on as userUpdatedOn,
        user.deleted_on as userDeletedOn
        FROM paper 
        INNER JOIN user ON paper.user_id = user.id
        ORDER BY paper.code ASC";
        $result = $this->findByQueryString($sql);
        return (object) $result;
    }

    public function validateFindUser(array | object $data): object
    {
        $data = (object)$data;
        if(filter_var($data->email, FILTER_VALIDATE_EMAIL) === false){
            $this->addError("email", "Kindly provide a valid email adress");
        }

        if(empty($this->authUserEmail($data->email))){
            $this->addError("email", "Email provided does not exist");
        }
        return (Object) $this->getErrors();
    }

}