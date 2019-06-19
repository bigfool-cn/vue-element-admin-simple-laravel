<?php
/**
 * Created by PhpStorm.
 * User: oray
 * Date: 2019/6/14
 * Time: 12:08
 */

namespace App\Validates;


use Illuminate\Support\Facades\Validator;

class BaseValidate
{
    /***
     * 验证字段属性
     *
     */
    protected $input;

    /**
     * 重写验证场景
     * @param $inputs
     * @param $scene
     * @return bool|string
     */
    public function check($inputs, $scene=''){
        $input = $this->getInput($inputs, $scene);
        $rules = $this->getRules($scene);
        $messages  = $this->getMessage($rules);
        $validator = Validator::make($input, $rules, $messages);

        //返回错误信息
        if ($validator->fails()) {
            return $validator->errors()->first(); //返回错误信息
        }
        return false;
    }

    //获取验证数据
    public function getInput($inputs, $scene)
    {
        if (isset($this->scene)) {
            if ($this->scene[$scene]){
                $rules = $this->scene[$scene];
            } else {
                $rules = array_keys($this->rule);
            }
        } else {
            $rules = array_keys($this->rule);
        }
        foreach ($rules as $key=>$v){
            if (array_key_exists($v, $inputs)){
                $input[$v] = $inputs[$v];
            }
        }
        if (isset($inputs['password_confirm'])) {
            $input['password_confirmation'] = $inputs['password_confirm'];
        }
        return $input ?? array();
    }

    /**
     * 获取验证规则
     * @param $scene
     * @return mixed
     */
    public function getRules($scene)
    {
        if (isset($this->scene)) {
            if ($this->scene[$scene]){
                foreach ($this->scene[$scene] as $field){
                    if (array_key_exists($field, $this->rule)){
                        $rules[$field] = $this->rule[$field];
                    }
                }
            } else {
                $rules = $this->rule;
            }
        } else {
            $rules = $this->rule;
        }
        return $rules;
    }


    /***
     * 返回验证message
     * @return array
     */
    public function getMessage($rules){
        foreach ($rules as $key=>$v){
            $arr = explode('|',$v);
            foreach($arr as $k=>$val){
                if (strpos($val,':')){
                    unset($arr[$k]);
                    $arr[] = substr($val,0, strpos($val, ':'));
                }
            }
            foreach($arr as $value){
                if (array_key_exists($key . '.' . $value, $this->message)){
                    $message[$key . '.' . $value] = $this->message[$key . '.'. $value];
                }
            }
        }
        return $message;
    }
}
