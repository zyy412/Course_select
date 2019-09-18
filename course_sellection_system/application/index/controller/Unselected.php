<?php
/**
 * 还未选择的课程
 */
namespace app\index\controller;
use think\Controller;
class Unselected extends Important{
    /**
     * 未选择的课程列表
     */
    public function lst(){
        $s_num = session('id');
        $data1= \think\Db::name("student")->where('s_num',$s_num)->find();
        $planPath = \think\Db::name("plan")->field('p_path')->where('p_major',$data1['s_major'])->where('p_grade',$data1['s_grade'])->find();
        $path = '#';
        if($planPath){
            $path = '/uploads/' . $planPath['p_path'];    
        }
        $this->assign('planPath',$path);
        $major1 = $data1['s_major'];
        $major = preg_replace('/\(.*?\)/', '', $major1);
        $grade = $data1['s_grade'];
        $now = date("Y");//当前年
        $month = (int)date("m");//当前月份
        if($now == $grade){
            if($month==9||$month==10||$month==11||$month==12){
                $semester = 2*($now-$grade+1);
            }
        }else{
            if($month==1||$month==2||$month==9||$month==10||$month==11||$month==12){
                $semester = 2*($now-$grade+1);
            }else{
                $semester = 2*($now-$grade)+1;
            }
        }
        $data2 = \think\Db::table('system_major')->field('m_id')->where('major_name',$major)->where('major_grade',$grade)->find();
        $m_id = $data2['m_id']; 
        $data3 = \think\Db::table("system_"."$m_id"."_course")->field('c_id,lang')->where('s_num',$s_num)->where('status',0)->where('semester',$semester)->select();  
        $c_id = array_map('array_shift', $data3);
        $arr = array_map('array_pop', $data3);
        $str = trim(trim(array_pop($arr),'('),')');
        $len = strlen($str)-1;
        if($str){
            $lang = explode(',',$str);
            $this->assign('lang',$str{$len});
        }
        $data4 = \think\Db::name("course")->where('c_id',"in",$c_id)->select();
        
        $this->assign('course',$data4);
        
        return $this->fetch();
    }
    /**
     * 选择课程
     */
    public function choose(){
        $chooseTime = \think\Db::name("choose_time")->order("id desc")->find();
        $nowTime = time();
        if($nowTime<$chooseTime['begin_time']){
            $this->error('选课时间未到！');
        }elseif($nowTime>$chooseTime['end_time']){
            $this->error('已过选课时间！');
        }else{
            $s_num = session('id');
            $data1= \think\Db::name("student")->where('s_num',$s_num)->find();
            $major1 = $data1['s_major'];
            $major = preg_replace('/\(.*?\)/', '', $major1);
            $grade = $data1['s_grade'];
            $data2 = \think\Db::table('system_major')->field('m_id')->where('major_name',$major)->where('major_grade',$grade)->find();
            $m_id = $data2['m_id']; 
            $c_id =  input("c_id");
            $id = $s_num . $c_id;
            $db=\think\Db::table("system_"."$m_id"."_course")->where('id',$id)->update(['status'=>1,'update_time'=>date('Y-m-d H:i:s',time())]);
            if($db){
                $log = [
                    'op_name'=>$s_num,
                    'op_type'=>'choose course',
                    'op_time'=>date('Y-m-d H:i:s',time()),
                    'op_ip'=>$_SERVER['REMOTE_ADDR'],
                    'op_bak'=>"选择了课程ID="."$c_id".'的课程'
                ];
                \think\Db::table("op_log")->insert($log);
                return $this->success('选择成功！','studentcourse/lst');
            }else{
                return $this->error('出错啦~','lst');
            }
        }       
    }
    /**
     * 未选择的课程详细信息
     * 详细信息中随意修改URL栏，会取到课程信息。后期修改时，进行限制。
     */
    public function details(){
        $c_id = input('c_id');
        if($c_id){
            $data = \think\Db::name("course")->where('c_id',$c_id)->find();
            $this->assign('course',$data);
            return $this->fetch();
        }else{
            $this->error('页面出错~');
        }
    }
}