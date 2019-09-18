<?php
namespace app\manage\controller;
use think\Controller;
header("Content-Type: text/html; charset=utf-8");
class Choose extends Base{
    public function index(){
        $majorName = \think\Db::name('major')->group('major_name')->select();
        $majorGrade = \think\Db::name('major')->group('major_grade')->order('major_grade','desc')->select();
        $this->assign('majorGrade',$majorGrade);
        $this->assign('majorName',$majorName);
        return $this->fetch();
    }
    public function check(){
        if(request()->isPost()){
            // $major1 = input('major');
            $major = input('major');
            // $major = preg_replace('/\(.*?\)/', '', $major1);
            $grade = input('grade');
            $major = \think\Db::name('major')->field('m_id')->where('major_name',$major)->where('major_grade',$grade)->find();
            $m_id = $major['m_id'];
            $cidArray = array();
            $cidArray = \think\Db::name("$m_id" .  "_course")->field('c_id')->group('c_id')->select();
            if($cidArray){
                foreach($cidArray as $key=>$value){
                    foreach($value as $v){
                        $course[] = \think\Db::table('system_course')->where('c_id',$v)->find();
                        $course[$key]['sum'] = count(\think\Db::name($m_id . '_course')->where('c_id',$v)->where('status',1)->select());
                    }
                }
                $this->assign('m_id',$m_id);
                $this->assign('course',$course);//课程信息
                return $this->fetch();
            }else{
                $this->error("您还没有分配课程！");
            }
        }
    }
    //选课名单
    public function studentlst(){
        $m_id = input('m_id');
        $c_id = input('c_id');
        $tableId = array();
        $s_num = array();
        $s_num = \think\Db::name( $m_id . '_course')->field('s_num')->where('c_id',$c_id)->where('status',1)->order('s_num desc')->select();
        $data = array();
        foreach($s_num as $value){
            $data[] = $value['s_num'];
        }
        $length = count($s_num);
        $studentinfo = \think\Db::table('system_student')->where('s_num','in',$data)->field('s_num,name,s_class')->order('s_class asc')->paginate(15);
        $page = $studentinfo->render();
        
        if($studentinfo){
            $this->assign('page',$page);
            $this->assign('studentinfo',$studentinfo);
            return $this->fetch();
        }else{
            $this->error('没有学生选择这门课');
        }
    }
    /**
     * 导出名单
     */
    public function exportNameLst(){
        $m_id = input('m_id');
        $c_id = input('c_id');
        $tableId = array();
        $s_num = array();
        $courseName = \think\Db::name('course')->field('c_name')->where('c_id',$c_id)->find();
        $major = \think\Db::name('major')->field('major_name,major_grade')->where('m_id',$m_id)->find();
        $s_num = \think\Db::name( $m_id . '_course')->field('s_num')->where('c_id',$c_id)->where('status',1)->order('s_num desc')->select();
        $data = array();
        foreach($s_num as $value){
            $data[] = $value['s_num'];
        }
        $studentinfo = \think\Db::table('system_student')->where('s_num','in',$data)->field('s_num,name,s_class')->order('s_class asc')->order('s_num asc')->select();
        if($studentinfo){
            require PUBLIC_PATH . '/tools/phpexcel/exportchoosed.php';
            $name = $major['major_name'] . $major['major_grade'] .'级《'. $courseName['c_name'] .'》选课名单';
            export($studentinfo,$name);
        }else{
            $this->error('暂无数据');
        }
    }
    public function select(){
        if(request()->isPost()){
            // 把查询条件传入查询方法
            $s_num = input('s_num');
            $stu_info = \think\Db::name('student')->field('s_num,s_major,s_grade')->where('s_num',$s_num)->find();
            $table_id = \think\Db::name('major')->field('m_id')->where('major_name',$stu_info['s_major'])->where('major_grade',$stu_info['s_grade'])->find();
            // $data = \think\Db::name($table_id['m_id'] .  "_course")->where('s_num',$s_num)->select();
            // var_dump($data);
            $data3 = \think\Db::table("system_".$table_id['m_id']."_course")->field('c_id,semester,all,status')->where('s_num',$s_num)->select();  
            $c_id = array_map('array_shift', $data3);
            $arr = array_map('array_pop', $data3);
            $str = trim(trim(array_pop($arr),'('),')');
            $len = strlen($str)-1;
            if($str){
                $lang = explode(',',$str);
                $this->assign('lang',$str{$len});
            }
            $data4 = \think\Db::name("course")->field('c_id,c_num,c_name')->where('c_id',"in",$c_id)->select(); 
            $length = count($data3);
            for($i=0;$i<$length;$i++){
                $data[$i]['c_id'] = $data3[$i]['c_id'];
                $data[$i]['c_num'] = $data4[$i]['c_num'];
                $data[$i]['c_name'] = $data4[$i]['c_name'];
                $data[$i]['semester'] = $data3[$i]['semester'];
                $data[$i]['all'] = $data3[$i]['all'];
                $data[$i]['status'] = $data3[$i]['status'];
            }
            $this->assign('data',$data);
            $this->assign('student',$s_num);
            $this->assign('table',$table_id['m_id'] . '_course'); 
            return $this->fetch('selectresult');
        }
        return $this->fetch();
    }
    public function select_action(){
        $c_id = input('c_id');
        $s_num = input('s_num');
        $table = input('table');
        $id = $s_num . $c_id;
        $result = \think\Db::name($table)->where('id',$id)->update(['status'=>1,'update_time'=>date('Y-m-d H:i:s',time())]);
        if($result){
            $log = [
                'op_name'=>'admin',
                'op_type'=>'choose course',
                'op_time'=>date('Y-m-d H:i:s',time()),
                'op_ip'=>$_SERVER['REMOTE_ADDR'],
                'op_bak'=>"admin帮". $s_num ."选择了课程ID="."$c_id".'的课程'
            ];
            \think\Db::table("op_log")->insert($log);
            echo "<script> alert('选择成功');history.go(-2)</script>";
        }else{
            $this->error('选择失败','index');
        }
    }
    public function unselect(){
        $c_id = input('c_id');
        $s_num = input('s_num');
        $table = input('table');
        $id = $s_num . $c_id;
        $result = \think\Db::name($table)->where('id',$id)->where('all','neq','1')->update(['status'=>0,'update_time'=>date('Y-m-d H:i:s',time())]);
        if($result){
            $log = [
                'op_name'=>'admin',
                'op_type'=>'remove course',
                'op_time'=>date('Y-m-d H:i:s',time()),
                'op_ip'=>$_SERVER['REMOTE_ADDR'],
                'op_bak'=>"admin帮". $s_num ."退选了课程ID="."$c_id".'的课程'
            ];
            \think\Db::table("op_log")->insert($log);
            echo "<script> alert('退选成功');history.go(-2)</script>";
        }else{
            echo "<script> alert('该课程是限选课，不能退选!');history.go(-2)</script>";
        }
    }
    public function countall(){
        if(request()->isPost()){
            $years = input('year');
            $next = $years+1;
            $semester = input('semester');
            // $month = array();
            if($semester==1){
                $month = "('$years-03','$years-04','$years-05','$years-06','$years-07','$years-08')";
            }else{
                $month = "('$next-01','$next-02','$years-09','$years-10','$years-11','$years-12')";
            }
            $table = \think\Db::name("major")->field('m_id')->select(); 
            $length = count($table);
            $data=array();
            for($i=0;$i<$length;$i++){
                $sql1 = "select c.c_id,co.c_num,co.c_major,co.c_semester,co.c_remarks,co.c_grade,co.c_name,count(*) as total from system_".$table[$i]['m_id']."_course c,system_course co where c.c_id=co.c_id and date_format(c.create_time,'%Y-%m') in $month group by c.c_id;";
                $sql2 = "select c.c_id,co.c_name,count(*) as total from system_".$table[$i]['m_id']."_course c,system_course co where c.c_id=co.c_id and date_format(c.create_time,'%Y-%m') in $month and c.status=1 group by c.c_id;";
                $result1 = \think\Db::query($sql1);
                $result2 = \think\Db::query($sql2);
                $res=array();
                $len = count($result1);

                for($j=0;$j<$len;$j++){
                    $res[$j] = [
                        'c_id'=>$result1[$j]['c_id'],
                        'c_num'=>$result1[$j]['c_num'],
                        'c_name'=>$result1[$j]['c_name'],
                        'c_grade'=>$result1[$j]['c_grade'],
                        'c_major'=>$result1[$j]['c_major'],
                        'c_semester'=>$result1[$j]['c_semester'],
                        'c_remarks'=>$result1[$j]['c_remarks'],
                        'total'=>$result1[$j]['total'],
                        'choose'=>$result2[$j]['total']
                    ];
                    array_push($data,$res[$j]);
                }
            }
            $this->assign('data',$data);
            return $this->fetch('countres');
        }
        return $this->fetch();
    }
    public function export(){
        echo '开发ing...';
    }
}