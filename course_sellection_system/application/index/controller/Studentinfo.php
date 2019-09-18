<?php
/**
 * 学生信息
 */
namespace app\index\controller;
use think\Controller;
class Studentinfo extends Important{
    public function index(){
        $s_num = session('id');
        $data = \think\Db::name("student")->where('s_num',$s_num)->find();
        $planPath = \think\Db::name("plan")->field('p_path')->where('p_major',$data['s_major'])->where('p_grade',$data['s_grade'])->find();
        $path = '#';
        if($planPath){
            $path = '/uploads/' . $planPath['p_path'];    
        }
        $this->assign('planPath',$path);
        $this->assign('student',$data);
        return $this->fetch();
    }
    public function changemail(){
        if(request()->isPost()){
            $pwd = md5(input('password'));
            
            $mail = input('email');
            $s_num = session('id');
            $password = \think\Db::name("student")->field('password')->where('s_num',$s_num)->find();
            if($pwd == $password['password']){
                $db= \think\Db::name('student')->where('s_num',$s_num)->update(['s_mail' => $mail]);
                if($db){
                    $log = [
                        'op_name'=>$s_num,
                        'op_type'=>'changeEmail',
                        'op_time'=>date('Y-m-d H:i:s',time()),
                        'op_ip'=>$_SERVER['REMOTE_ADDR'],
                        'op_bak'=>"修改邮箱为$mail"
                    ];
                    \think\Db::table("op_log")->insert($log);
                    $this->redirect('/index/studentinfo/index');
                }else{
                    $this->error('修改失败');
                }
            }else{
                $this->error('您输入的密码不正确');
            }
        }
    }
    public function changepwd(){
        $s_num = session('id');
        $data = \think\Db::name("student")->field('s_mail')->where('s_num',$s_num)->find();
        if(empty($data['s_mail'])){
            $this->error('修改失败，请先修改您的邮箱信息');
        }else{
            if(request()->isPost()){
                $pwd = md5(input('password'));
                $newPassword = md5(input('newPassword'));
                $password = \think\Db::name("student")->field('password')->where('s_num',$s_num)->find();
                if($pwd == $password['password']){
                    $db= \think\Db::name('student')->where('s_num',$s_num)->update(['password' => $newPassword]);
                    if($db){
                        $log = [
                            'op_name'=>$s_num,
                            'op_type'=>'changePWD',
                            'op_time'=>date('Y-m-d H:i:s',time()),
                            'op_ip'=>$_SERVER['REMOTE_ADDR'],
                            'op_bak'=>"已修改密码"
                        ];
                        \think\Db::table("op_log")->insert($log);
                        $this->success('修改成功,系统将退出，请重新登录。','login/logout');
                    }else{
                        $this->error('修改失败');
                    }
                }else{
                    $this->error('您输入的密码不正确');
                }
            }
        }
        
    }
}