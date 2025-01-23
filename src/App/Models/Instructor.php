<?php
declare(strict_types=1);
namespace App\Models;

use Framework\Model;
use Framework\Helpers\Session;
use Framework\Helpers\Redirect;
use Framework\Helpers\Functions;
use Framework\Helpers\CSV;

class Instructor extends Model
{
    // protected $table = "instructor";

    public function instructorAuth($code): object | bool       
    {
        $paper = $this->findByField("code", $code, 'paper');
        if(empty($paper)) {
            Session::set('warning','Paper does not exist');
            Redirect::to('instructor/dashboard');
        }elseif($paper->user_id !== Session::get('id')){
            Session::set('warning','Paper authorization error');
            Redirect::to('instructor/dashboard');
        }else{
            return $paper;
        }
    }

    public function validateQuestion(array | object $data): object
    {
        $data = (object)$data;
        if(empty($data->question) || $data->question === '<p></p>') {
            $this->addError('question', "Question field is required");
        }
      
        if(empty(array_filter($data->options)) || count(array_filter($data->options)) < 2){
            $this->addError('options', "Kindly add more than one Option");
        }

        if(!in_array('1', (array) $data->corrects) || !in_array('0', (array) $data->corrects)){
            $this->addError('corrects', "Kindly provide atleast one correct and one wrong answer");
        }

        return (Object) $this->getErrors();
    }
    public function validateNewTest(array|object $data): object
    {
        $data = (object)$data;
        if(empty($data->name)){
            $this->addError('name', "Full Name field is required");
        }

        if(!is_int((int) $data->time) || $data->time < 1 || $data->time > 1000){
            $this->addError('time', "Please enter a valid time in minuites less than 1000");
        }

        if(!is_int((int) $data->pass_mark) || $data->pass_mark <= 0 || $data->pass_mark >= 100){
            $this->addError('pass_mark', "Please enter a valid Pass Mark between 40 and 80");
        }

        if(!is_int((int) $data->poll) || $data->poll < 1){
            $this->addError('poll', "Please enter a valid Number");
        }
        return (Object) $this->getErrors();
    }

    public function validateUpdateTest(array|object $data): object
    {
        $data = (object)$data;

        if(!is_int((int) $data->time) || $data->time < 1 || $data->time > 1000){
            $this->addError('time', "Please enter a valid time in minuites less than 1000");
        }

        if(!is_int((int) $data->pass_mark) || $data->pass_mark <= 0 || $data->pass_mark >= 100){
            $this->addError('pass_mark', "Please enter a valid Pass Mark between 40 and 80");
        }
        
        if(!is_int((int) $data->poll) || $data->poll < 1){
            $this->addError('poll', "Please enter a valid Number");
        }
        return (Object) $this->getErrors();
    }

    public function paperCode(): string
    {
        do{
            $code = Functions::generateRandomCode();
        }while($this->fieldValueExists('code', $code, 'paper'));
        return $code;
    }

    public function insertNewQuestion($post, $paper)
    {
        Functions::createFolder("{$_ENV['CSV_PATH']}/papers");
        $csv = new CSV( $paper->code, 'papers');
        $answers = [];
        $hashes = [];
        foreach ($post['options'] as $key => $option) {
            do{
                $hash = Functions::generateRandomCode();
            }while(in_array($hash, $hashes));
            array_push($hashes, $hash); 
            $ans			    = [];
            $ans['answer']		= $option;
            $ans['correct']		= $post['corrects'][$key];
            $ans['hash']		= $hash;
            array_push( $answers,  $ans);
        }
        $csv->insertRows([
            'question'		=> $post['question'],	
            'image'			=> $post['image'],	
            'answers' 		=> json_encode($answers),
        ]);
    }


    public function updateQuestion($post, $paper)
    {
        Functions::createFolder("{$_ENV['CSV_PATH']}/papers");
        $csv = new CSV($paper->code,'papers');
        $answers = [];
        $hashes = [];
        foreach ($post['options'] as $key => $option) {
            do{
                $hash = Functions::generateRandomCode();
            }while(in_array($hash, $hashes));
            array_push($hashes, $hash); 
            $ans				= [];
            $ans['answer']		= $option;
            $ans['correct']		= $post['corrects'][$key];
            $ans['hash']		= $hash;
            array_push($answers, $ans);
        }
        $csv->updateRows([
            'question'		=> $post['question'],	
            'image'			=> $post['image'],	
            'answers' 		=> json_encode($answers),
        ], $post['id']);
    }


    public function getAllMyStudents($user_id): array | object
    {
        $sql = "SELECT * FROM `user` WHERE id IN (
                    SELECT user_id  FROM `result` WHERE paper_id IN (
                        SELECT id FROM `paper` WHERE user_id = {$user_id})) ORDER BY user.name ASC";
    
        $result = $this->findQueryString($sql);
        return (object) $result;
    }

    public function getAllTestStudents($paper_id): array | object
    {
        $sql = "SELECT DISTINCT *,
        result.id as resultId,
        result.created_on as resultCreatedOn,
        result.updated_on as resultUpdatedOn,
        result.deleted_on as resultDeletedOn,
        user.id as userId,
        user.created_on as userCreatedOn,
        user.updated_on as userUpdatedOn,
        user.deleted_on as userDeletedOn
        FROM user 
        INNER JOIN result ON user.id = result.user_id
        WHERE result.paper_id = {$paper_id}
        AND result.deleted_on IS NULL 
        AND user.deleted_on IS NULL
        ORDER BY user.name ASC";

        $result = $this->findQueryString($sql);
        return (object) $result;
    }

    public function myStudentResult($student_id): array | object
    {
        $teacher_id = Session::get('id');
        $sql = "SELECT result.*, paper.*,
        result.id as resultId,
        result.user_id as resultUserId,
        result.poll as resultPoll,
        result.created_on as resultCreatedOn,
        result.updated_on as resultUpdatedOn,
        result.deleted_on as resultDeletedOn,
        paper.id as paperId,
        paper.user_id as paperUserId,
        paper.poll as paperPoll,
        paper.created_on as paperCreatedOn,
        paper.updated_on as paperUpdatedOn,
        paper.deleted_on as paperDeletedOn
        FROM result 
        INNER JOIN paper ON result.paper_id = paper.id
        WHERE paper.user_id = {$teacher_id} 
        AND result.user_id = {$student_id}
        AND result.deleted_on IS NULL 
        AND paper.deleted_on IS NULL
        ORDER BY paper.name ASC";

        $result = $this->findQueryString($sql);
        return (object) $result;
    }
}