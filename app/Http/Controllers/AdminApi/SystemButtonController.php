<?php
/**
 * Created by PhpStorm.
 * User: JS_chen
 * Date: 2019/6/14
 * Time: 23:08
 */

namespace App\Http\Controllers\AdminApi;


use App\Models\SystemButton;
use App\Validates\SystemButtonValidate;
use Illuminate\Http\Request;

class SystemButtonController extends AdminApiController
{
    /**
     * 按钮Model
     * @var null
     */
    private $_systemButtonModel = null;

    /**
     * 按钮验证器
     * @var null
     */
    private $_systemButtomValidate = null;

    public function __construct()
    {
        $this->_systemButtonModel    = new SystemButton();
        $this->_systemButtomValidate = new SystemButtonValidate();
    }

    /**
     * 新增按钮
     * @return false|string
     */
    public function createSystemButton(Request $request)
    {
        $post = $request->all();
        $validate = $this->_systemButtomValidate->check($post,'create');
        if (!$validate) {
            try {
                $button_id = $this->_systemButtonModel->createSystemButton($post);
                if ($button_id) {
                    return $this->json(20000, '新增按钮成功');
                } else {
                    return $this->json(50000, '新增按钮失败');
                }
            } catch (\Exception $e) {
                return $this->json(50000, '新增按钮成功');
            }
        } else {
            return $this->json(40000, $validate);
        }
    }

    /**
     * 修改按钮
     * @return false|string
     */
    public function updateSystemButton(Request $request)
    {
        $post = $request->all();
        $validate = $this->_systemButtomValidate->check($post,'update');
        if (!$validate) {
            // 判断唯一标识是否存在
            $button = $this->_systemButtonModel->getSystemButton(array('key'=>$post['key']));
            if ($button && $button->button_id!=$post['button_id']) {
                return $this->json(40000, '唯一标识已经存在');
            }
            try {
                $data = array(
                    'title'     => $post['title'],
                    'key'       => $post['key'],
                    'is_enable' => $post['is_enable']
                );
                $res = $this->_systemButtonModel->updateSystemButton($post['button_id'],$data);
                if ($res) {
                    return $this->json(20000, '修改按钮成功');
                } else {
                    return $this->json(50000, '修改按钮失败');
                }
            } catch (\Exception $e) {
                return $this->json(50000, '修改按钮成功');
            }
        } else {
            return $this->json(40000, $validate);
        }
    }

    /**
     * 删除按钮
     * @return false|string
     */
    public function deleteSystemButton(Request $request)
    {
        $post = $request->all();
        $buttonId = $post['button_id'];
        if (empty($buttonId) && !is_int($buttonId) && $buttonId < 0) {
            return $this->json(40000,'参数ID错误');
        }
        try {
            $condition = array('button_id'=>$buttonId);
            $res = $this->_systemButtonModel->deleteSystemButton($condition);
            if ($res) {
                return $this->json(20000, '删除成功');
            } else {
                return $this->json(50000, '删除失败');
            }
        } catch (\Exception $e) {
            return $this->json(50000,'删除失败');
        }
    }

    /**
     * 更改是否可用
     * @return false|string
     */
    public function updateSystemButtonEnable(Request $request)
    {
        $post = $request->all();
        $validate = $this->_systemButtomValidate->check($post, 'update_enable');
        if (!$validate) {
            $data = array('is_enable'=>!$post['is_enable']);
            try {
                $res = $this->_systemButtonModel->updateSystemButton($post['button_id'],$data);
                if ($res) {
                    return $this->json(20000, '更改按钮状态成功');
                } else {
                    return $this->json(50000, '更改按钮状态失败');
                }
            } catch (\Exception $e) {
                return $this->json(50000, '更改按钮状态失败');
            }

        } else {
            return $this->json(40000, $validate);
        }
    }

    /**
     * 获取按钮列表
     * @return false|string
     */
    public function getSystemButtonList(Request $request)
    {
        $params = $request->all();
        $page   = 1;
        $row    = 20;
        $condition  = '1=1';
        $this->_makeCondition($params, $condition, $page, $row);
        $data = $this->_systemButtonModel->getSystemButtonPage($page, $row, $condition);
        return $this->json(20000,'获取成功', $data);
    }

    /**
     * 获取所有按钮
     * @return false|string
     */
    public function getSystemButtonAll(Request $request)
    {
        $is_enable = $request->input('is_enable',1);
        $condition = array('is_enable'=>$is_enable);
        $data = $this->_systemButtonModel->getSystemButtonAll($condition);
        return $this->json(20000,'获取成功', $data);
    }

    /**
     * 列表查询条件
     * @param $params
     * @param $condition
     * @param $page
     * @param $row
     */
    private function _makeCondition($params, &$condition, &$page, &$row)
    {
        if (empty($params)) {
            $page = 1;
            $row  = 20;
            return;
        }

        // 页码
        if (isset($params['page']) && $params['page'] > 0 && is_int((int)$params['page'])) {
            $page = $params['page'];
        } else {
            $page = 1;
        }

        // 数量
        if (isset($params['row']) && $params['row'] > 0 && is_int((int)$params['row']) && $params['row'] <= 100) {
            $row = $params['row'];
        } else {
            $row = 20;
        }

        // 按钮名称
        if (isset($params['title']) && !empty($params['title'])) {
            $condition .= " AND title LIKE '%{$params['title']}%'";
        }

        // 是否可用
        if (isset($params['is_enable']) && $params['is_enable'] !== '' && in_array($params['is_enable'], [0, 1])) {
            $condition && $condition .= ' AND ';
            $condition .= "is_enable = {$params['is_enable']}";
        }

        // 时间
        if (isset($params['date']) && strtotime($params['date'][0])) {
            $condition && $condition .= ' AND ';
            $condition .= "create_time >= '{$params['date'][0]}'";
        }
        if (isset($params['date']) && strtotime($params['date'][1])) {
            $condition && $condition .= ' AND ';
            $endTime = date('Y-m-d', strtotime ("+1 day", strtotime($params['date'][1])));
            $condition .= "create_time <= '$endTime'";
        }
    }
}
