# 左右值分类
## 基于Thinkphp5.0

如需在其他框架下使用，修改sql的函数即可

### 注意

> 必须要有主键,且字段为id

> 必须要有字段Lft,Rgt,level

> 父id的字段必须为pid

### 代码

```
<?php
namespace app\index\model;

use think\Db;

/**
 * 通用左右值数据结构 增加、编辑、删除操作
 * @package app\index\model
 */
class TreeLevel
{
    private $_table;

    public function __construct($table)
    {
        $this->_table   = $table;
    }

    /**
     * 添加
     * @param $res
     * @return bool
     */
    public function add($res)
    {
        $data = [];
        $data = $res;
        if($res['pid'] == 0){
            $tem = Db::table($this->_table)->order('Rgt desc')->find();
            if(empty($tem)){
                $data['Rgt'] = 2;
                $data['Lft'] = 1;
            }else{
                $data['Lft'] = $tem['Rgt']+1;
                $data['Rgt'] = $tem['Rgt']+2;
            }
            $data['level'] = 0;
            //插入
            if(Db::table($this->_table)->insert($data)){
                return true;
            }else{
                return false;
            }
        }else{
            $tem = Db::table($this->_table)->where(['id'=>$res['pid']])->find();
            $data['Lft'] = $tem['Rgt'];
            $data['Rgt'] = $tem['Rgt']+1;
            $data['level'] = $tem['level']+1;
            Db::startTrans();
            //更新左右值
            $sql = 'UPDATE '.$this->_table.' SET `Lft`=`Lft`+2 WHERE `Lft`>'.$tem['Rgt'];
            if(Db::execute($sql)===false){
                Db::rollback();
                return false;
            }
            unset($sql);
            $sql = 'UPDATE '.$this->_table.' SET `Rgt`=`Rgt`+2 WHERE `Rgt` >='.$tem['Rgt'];
            if(Db::execute($sql)===false){
                Db::rollback();
                return false;
            }
            //插入
            if( Db::table($this->_table)->insert($data) ){
                Db::commit();
                return true;
            }else{
                Db::rollback();
                return false;
            }
        }
    }

    /**
     * 移动（编辑）
     * @param $res
     * @return bool
     */
    public function move($res)
    {
        if ($res['id'] == 1) {
            return false;
        }
        $info = Db::table($this->_table)->where(['id'=>$res['id']])->find();
        if(empty($info)){
            return false;
        }
        //不对分类树进行改变
        if($res['pid'] == $info['pid']){
            if( Db::table($this->_table)->where(['id'=>$res['id']])->update($res) ){
                return true;
            }else{
                return false;
            }
        }

        $son_cids   = $this->getSonCid($info['id']);
        $son_cids   = implode(',', $son_cids);
        $new_father = Db::table($this->_table)->where(['id'=>$res['pid']])->find();
        $old_father = Db::table($this->_table)->where(['id'=>$info['pid']])->find();

        $param = '';
        if($new_father['level'] > $old_father['level']){
            $upload_level = $new_father['level']+1-$info['level'];
            $param = ',`level` = `level` + '.$upload_level;
        }else if($new_father['level'] < $old_father['level']){
            $upload_level = $info['level'] - $new_father['level']-1;
            $param = ',`level` = `level` - '.$upload_level;
        }

        $update_nums = $info['Rgt']-$info['Lft'];
        if($new_father['Rgt']>$info['Rgt']){
            $update_lft_sql = 'UPDATE '.$this->_table.' SET `Lft`=`Lft`-'.$update_nums.'-1 WHERE `Lft`>'.$info['Rgt'].
                ' AND `Rgt` <= '.$new_father['Rgt'];
            $update_rgt_sql = 'UPDATE '.$this->_table.' SET `Rgt`=`Rgt`-'.$update_nums.'-1 WHERE `Rgt`>'.$info['Rgt'].
                ' AND `Rgt` < '.$new_father['Rgt'];
            $self_nums = $new_father['Rgt']-$info['Rgt']-1;
            if($new_father['level'] == $old_father['level']){
                $update_self_sql = 'UPDATE '.$this->_table.' SET `Rgt`=`Rgt`+'.$self_nums.',`Lft`=`Lft`+'.$self_nums.
                    ' WHERE `id` in ('.$son_cids.')';
            }else{
                $update_self_sql = 'UPDATE '.$this->_table.' SET `Rgt`=`Rgt`+'.$self_nums.',`Lft`=`Lft`+'.$self_nums.$param.
                    ' WHERE `id` in ('.$son_cids.')';
            }

        }else{
            $update_lft_sql = 'UPDATE '.$this->_table.' SET `Lft`=`Lft`+'.$update_nums.'+1 WHERE `Lft`<'.$info['Lft'].
                ' AND `Lft` > '.$new_father['Rgt'];
            $update_rgt_sql = 'UPDATE '.$this->_table.' SET `Rgt`=`Rgt`+'.$update_nums.'+1 WHERE `Rgt`<'.$info['Lft'].
                ' AND `Rgt` >= '.$new_father['Rgt'];
            $self_nums = $info['Lft']-$new_father['Rgt'];
            if($new_father['level'] == $old_father['level']){
                $update_self_sql = 'UPDATE '.$this->_table.' SET `Rgt`=`Rgt`-'.$self_nums.',`Lft`=`Lft`-'.$self_nums.
                    ' WHERE `id` in ('.$son_cids.')';
            }else{
                $update_self_sql = 'UPDATE '.$this->_table.' SET `Rgt`=`Rgt`-'.$self_nums.',`Lft`=`Lft`-'.$self_nums.$param.
                    ' WHERE `id` in ('.$son_cids.')';
            }
        }

        Db::startTrans();
        if( Db::execute($update_lft_sql) === false ){
            Db::rollback();
            return false;
        }
        if( Db::execute($update_rgt_sql)===false ){
            Db::rollback();
            return false;
        }
        if( Db::execute($update_self_sql)===false ){
            Db::rollback();
            return false;
        }
        if( Db::table($this->_table)->where(array('id'=>$info['id']))->update($res) ){
            Db::commit();
            return true;
        }else{
            Db::rollback();
            return false;
        }
    }

    /**
     * 删除
     * @param $id
     * @return bool
     */
    public function del($id)
    {
        if ($id == 1) {
            return false;
        }
        $info = Db::table($this->_table)->where(['id'=>$id])->find();
        if(empty($info)){
            return false;
        }
        //是否有子孙分类
        $num = Db::table($this->_table)->where(['pid'=>$info['id']])->count();
        if($num>0){
            return false;
        }
        unset($num);
        Db::startTrans();
        //更新左右值
        $sql = 'UPDATE '.$this->_table.' SET `Rgt`=`Rgt`-2 WHERE `Rgt`>'.$info['Rgt'];
        if(false === Db::execute($sql)){
            Db::rollback();
            return false;
        }
        unset($sql);
        $sql = 'UPDATE '.$this->_table.' SET `Lft`=`Lft`-2 WHERE `Lft`>'.$info['Lft'];
        if(false === Db::execute($sql)){
            Db::rollback();
            return false;
        }
        //删除
        if(Db::table($this->_table)->where(['id'=>$info['id']])->delete()){
            Db::commit();
            return true;
        }else{
            Db::rollback();
            return false;
        }
    }


    /**
     * 获取子类
     * @param int $cid
     * @return array|bool
     */
    protected function getSonCid($cid=0){
        if(empty($cid)){
            $info['Rgt'] = Db::table($this->_table)->max('Rgt');
            $info['Lft'] = 1;
        }else{
            $info = Db::table($this->_table)->where(['id'=>$cid])->find();
            if(empty($info)){
                return false;
            }
        }
        $data = array();
        $data['Lft'] = array('EGT',$info['Lft']);
        $data['Rgt'] = array('ELT',$info['Rgt']);

        $tem = Db::table($this->_table)->where($data)->field('id')->select();
        $return = array();
        foreach ($tem as $k => $v){
            $return[]=$v['id'];
        }
        return $return;
    }
}
```