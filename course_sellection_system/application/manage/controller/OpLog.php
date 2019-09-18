<?php
namespace app\manage\controller;
use think\Controller;
header("Content-Type: text/html; charset=utf-8");
class OpLog extends Base{
    public function index(){
        return $this->fetch();
    }
    public function admin_op_log(){

    }
    //xuesheng
    public function op_log(){
        $result = \think\Db::table('system.op_log')->order("id desc")->paginate(15);
        $page = $result->render();
        $this->assign('page',$page);
        $this->assign('data',$result);
        return $this->fetch();
    }
    public function searchop(){
        if(request()->isPost()){
            // 把查询条件传入查询方法
            $s_num = input('s_num');
            // dump($data);die();
            $result = \think\Db::table('system.op_log')->where('op_name',$s_num)->order("id desc")->select();
            if($result){
                $this->assign('data',$result);
                return $this->fetch('searchlst');
            }else{
                return $this->error('查询失败，暂无此数据，请返回查证后重试！');
            }

        }
        return $this->fetch();
    }
}