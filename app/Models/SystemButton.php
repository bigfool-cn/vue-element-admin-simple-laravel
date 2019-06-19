<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemButton extends Model
{
    /**
     * 数据表主键
     * @var string
     */
    protected $primaryKey = 'button_id';

    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'system_button';

    /**
     * 可以被批量赋值的属性.
     *
     * @var array
     */
    protected $fillable = ['key','title','is_enable','update_time','create_time'];

    /**
     * 关闭自动更新时间字段
     * @var bool
     */
    public $timestamps = false;

    /**
     * 新增按钮
     * @param array $data
     * @return mixed
     */
    public function createSystemButton($data=array())
    {
        $data['create_time'] = date('Y-m-d H:i:s');
        $model = self::create($data);
        return $model->button_id;
    }

    /**
     * 修改按钮
     * @param int $buttonId
     * @param array $data
     * @return int|string
     */
    public function updateSystemButton($buttonId=0, $data=array())
    {
        $data['update_time'] = date('Y-m-d H:i:s');
        $model = self::where('button_id',$buttonId)->update($data);
        return $model;
    }

    /**
     * 获取单条按钮
     * @param array $condition
     * @param array $fields
     * @return mixed
     */
    public function getSystemButton($condition=array(), $fields=array('*'))
    {
        $model = self::where($condition)->select($fields)->first();
        return $model;
    }

    /**
     * 删除按钮
     * @param array $condition
     * @return int
     */
    public function deleteSystemButton($condition=array())
    {
        $model = self::where($condition)->delete();
        return $model;
    }

    /**
     * 获取按钮分页
     * @param int $page
     * @param int $row
     * @param string $condition
     * @return array
     */
    public function getSystemButtonPage($page=1, $row=20, $condition='1=1')
    {
        $paginate = self::whereRaw($condition)->orderBy('create_time', 'DESC')->paginate($row);
        $system_buttons = $paginate->all();
        $pages = array(
            'current_page' => (int) $paginate->currentPage(),
            'last_page'    => (int) $paginate->lastPage(),
            'per_page'     => (int) $paginate->perPage(),
            'total'        => (int) $paginate->total(),
        );
        $data = array(
            'pages'          => $pages,
            'system_buttons' => $system_buttons
        );
        return $data;
    }

    /**
     * 获取所有按钮
     * @param array $condition
     * @param array $fields
     * @return mixed
     */
    public function getSystemButtonAll($condition=array(), $fields=array('*'))
    {
        $query = self::where($condition);
        isset($condition['button_id']) && $query = self::whereIn('button_id',$condition['button_id']);
        $model = $query->get($fields)->toArray();
        return $model;
    }
}
