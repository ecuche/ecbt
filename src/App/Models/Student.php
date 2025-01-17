<?php
declare(strict_types=1);
namespace App\Models;

use Framework\Helpers\CSV;
use Framework\Model;
use Framework\Helpers\Session;
use Framework\Helpers\Redirect;
use Framework\Helpers\Functions;

class Student extends Model
{
    // protected $table = "student";


    public function paperAuth($code): object | bool       
    {
        $paper = $this->findByField("code", $code, 'paper');
        $file_exist = file_exists( "{$_ENV['CSV_PATH']}/papers/{$paper->code}.csv");
        if(empty($paper) || !$file_exist) {
            Session::set('warning','Paper does not exist');
            Redirect::to('/dashboard');
        }else{
            return $paper;
        }
    }

    public function testAuth() : bool | array |object
    {
        $paper = (object) Session::get('paper');
        if(!empty($paper)){
            return $paper;
        }else{
            Session::set('warning','Kindly register to write this test');
            Redirect::to('/dashboard');
        }
    }

    public function resultAuth($user_id, $paper_id): object | bool
    {
        $result = $this->findByFields( ['user_id'=>$user_id, 'paper_id'=>$paper_id],'result');
        if(empty($result)) {
            Session::set('warning','Result Not Found');
            Redirect::to('/dashboard');
        }else{
            return $result;
        }
    }

    public function grade($score, $total): object|array
    {
        $percent = round(($score / $total) * 100, 2);
        
        if ($percent < 39.50) {
            $grade = 'F';
            $remark = 'Failed';
            $color = 'danger';
        } elseif ($percent < 49.50) {
            $grade = 'D';
            $remark = 'Pass';
            $color = 'warning';
        } elseif ($percent < 59.50) {
            $grade = 'C';
            $remark = 'Good';
            $color = 'info';
        } elseif ($percent < 69.50) {
            $grade = 'B';
            $remark = 'Very Good';
            $color = 'primary';
        } else {
            $grade = 'A';
            $remark = 'Excellent';
            $color = 'success';
        }
        
        return (object)[
            'score' => $score,
            'grade' => $grade,
            'remark' => $remark,
            'color' => $color,
            'total' => $total,
            'percent' => $percent
        ];
    }
    

    public function remTime(): int | bool
    {
        $end_time = Session::get('end_time');
        $rem_time = $end_time - time();    
        if (empty($end_time) || $rem_time <= 0) {
           return false;
        }
        return $rem_time;
    }

    public function createTestSheet($questions, $paper_code): array | object
    {
        $rand = Functions::generateRandomCode();
        $test_name = "{$paper_code}_{$_SESSION['id']}_{$rand}";
        Session::set("test_name", $test_name);
        Session::set("start_time", date('Y-m-d H:i:s'));
        $location = "{$_ENV['CSV_PATH']}/results";
        Functions::createFolder($location);
        $csv  = new CSV($test_name, 'results');
        foreach ($questions as $question) {
            $value = [];
            $value['chosen'] = '';
            $value['correct'] = '';
            $value['hash'] = '';
            $value['question_id'] = $question['id'];
            $value['question'] = $question['question'];
            $value['image'] = $question['image'];
            $answers = json_decode($question['answers'], true);
            shuffle($answers);
            $value['options'] = json_encode($answers);
            $csv->insertRows($value);
        }
        return $csv->getRow(1);
    }

    public function ValidateCode(array|object $data) : object
    {
        $data = (object)$data;
        $paper = $this->findByField("code", $data->code, 'paper');
        if(empty($paper)){
            $this->addError('code', 'Paper does not exist');
        }
        return (Object) $this->getErrors();
    }

    public function getUserResults($user_id): object
    {
        $sql = "SELECT *,
        result.id as userId,
        result.poll as resultPoll,
        result.created_on as resultCreatedOn,
        result.updated_on as resultUpdatedOn,
        result.deleted_on as resultDeletedOn,
        paper.id as paperId,
        paper.name as paperName,
        paper.poll as paperPoll,
        paper.created_on as paperCreatedOn,
        paper.updated_on as paperUpdatedOn,
        paper.deleted_on as paperDeletedOn,
        user.id as userId,
        user.name as userName,
        user.created_on as userCreatedOn,
        user.updated_on as userUpdatedOn,
        user.deleted_on as userDeletedOn
        FROM result 
        INNER JOIN paper on result.paper_id = paper.id
        INNER JOIN user on paper.user_id = user.id
        WHERE result.user_id = {$user_id}
        AND result.deleted_on IS NULL 
        AND paper.deleted_on IS NULL
        AND user.deleted_on IS NULL
        ORDER BY result.created_on DESC";
    
        $result = $this->findQueryString($sql);
        return (object) $result;
    }

}
