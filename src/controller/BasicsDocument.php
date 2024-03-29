<?php
/**
 * Class Deploy
 * @title 部署基础类
 */
namespace normphpCore\devDoc\controller;

use authority\app\Resource;
use normphp\staging\Controller;
use normphp\staging\Request;
use normphpCore\devDoc\service\DocumentService;
use ZipArchive;

class BasicsDocument extends Controller
{
    /**
     * 基础控制器信息
     */
    const CONTROLLER_INFO = [
        'User'=>'pizepei',
        'title'=>'文档控制器',//控制器标题
        'namespace'=>'',//门面控制器命名空间
        'basePath'=>'/document/',//基础路由
    ];
    /**
     * @param \normphp\staging\Request $Request
     *      path [object] 路径参数
     *          type [string] 路径
     * @return array [html]
     * @title  文档入口（开发助手）
     * @explain 文档入口（API文档、权限文档、公共资源文档）
     * @router get :type[string].html debug:true
     * @throws \Exception
     */
    public function index(Request $Request)
    {
        $name = $Request->path('type')==='index'?'document':$Request->path('type');
        $MODULE_PREFIX = \Deploy::MODULE_PREFIX===''?'':'/'.\Deploy::MODULE_PREFIX;
        $cdn = true;
        if (gethostbyname('baidu.com') ==='baidu.com'){$cdn =false;}
        $data = [
            '_NAV_'=>self::_NAV_,
            'local.layui.css'=>$cdn?'https://www.layuicdn.com/layui-v2.5.5/css/layui.css':$MODULE_PREFIX.'/document/resource/css/layui.css',
            'local.layui.js'=>$cdn?'https://www.layuicdn.com/layui-v2.5.5/layui.js':$MODULE_PREFIX.'/document/resource/js/layui.js',
            'MODULE_PREFIX'=>$MODULE_PREFIX,
            'jsonDataName'=>$this->app->__INIT__['returnJsonData'],
        ];
        $path = dirname(__DIR__).DIRECTORY_SEPARATOR.'template'.DIRECTORY_SEPARATOR.'Document'.DIRECTORY_SEPARATOR;
        return $this->view($name,$data,$path,'html',false);
    }
    /**
     * @Author pizepei
     * @Created 2019/7/5 22:40
     * @param \normphp\staging\Request $Request
     *      path [object]
     *          name [string] 文件名
     * @title  获取js
     * @explain  获取js
     * @throws \Exception
     * @return string [js]
     * @router get resource/js/:name[string].js
     */
    public function js(Request $Request)
    {
        $path = dirname(__DIR__).DIRECTORY_SEPARATOR.'template'.DIRECTORY_SEPARATOR.'document'.DIRECTORY_SEPARATOR.'layui'.DIRECTORY_SEPARATOR;
        if (is_file($path.$Request->path('name').'.js')){return file_get_contents($path.$Request->path('name').'.js');}
        return '';
    }
    /**
     * @Author pizepei
     * @Created 2019/7/5 22:40
     * @param \normphp\staging\Request $Request
     *      path [object]
     *          name [string] 文件名
     * @title  获取css
     * @explain  获取css
     * @throws \Exception
     * @return string [css]
     * @router get resource/css/:name[string].css
     */
    public function css(Request $Request)
    {
        $path = dirname(__DIR__).DIRECTORY_SEPARATOR.'template'.DIRECTORY_SEPARATOR.'document'.DIRECTORY_SEPARATOR.'layui'.DIRECTORY_SEPARATOR;
        if (is_file($path.$Request->path('name').'.css')){return file_get_contents($path.$Request->path('name').'.css');}
        return '';
    }
    /**
     * @Author pizepei
     * @Created 2019/7/5 22:40
     * @param \normphp\staging\Request $Request
     *      path [object]
     *          name [string] 文件名
     * @title  获取 modules js
     * @explain  获取 modules js
     * @throws \Exception
     * @return string [js]
     * @router get resource/js/lay/modules/:name[string].js
     */
    public function modulesJs(Request $Request)
    {
        $path = dirname(__DIR__).DIRECTORY_SEPARATOR.'template'.DIRECTORY_SEPARATOR.'document'.DIRECTORY_SEPARATOR.'layui'.DIRECTORY_SEPARATOR;
        if (is_file($path.$Request->path('name').'.js')){return file_get_contents($path.$Request->path('name').'.js');}
        return '';
    }
    /**
     * @param \normphp\staging\Request $Request html
     *      path [object] 路径参数
     *          name [string required] 扩展文件名称
     * @return array [js]
     * @title  文档layui Extends
     * @explain  文档layui Extends
     * @router get layui/extends/:name[string].js debug:true
     * @throws \Exception
     */
    public function layuiExtends(Request $Request)
    {
        $path = dirname(__DIR__).DIRECTORY_SEPARATOR.'template'.DIRECTORY_SEPARATOR.'Document'.DIRECTORY_SEPARATOR;
        return $this->view($Request->path('name'),[],$path,'js',false);

    }

    /**
     * @return array [json]
     *      data [raw] 菜单数据
     * @title  API文档 侧边导航
     * @explain  侧边导航
     * @router get nav-list debug:true
     * @throws \Exception
     */
    public function navList()
    {
        return $this->succeed($this->app->Route()->getNoteBlockDocument(),'',200);
    }
    /**
     * @Author pizepei
     * @Created 2019/2/12 23:01
     *
     * @param \normphp\staging\Request $Request
     *      get [object] 路径参数
     *          father [string] 父路径
     *          index [string] 当前路径
     * @return array [json]
     *      data [object] 控制器数据
     *          fatherInfo [object] 控制器数据
     *              baseAuthGroup [raw] baseAuthGroup
     *              index [string] 命名空间
     *              title [string] 控制器标题
     *              class [string] 控制器名
     *              User [string] 创建者
     *              route [raw] route
     *              basePath [string] 控制器根路由
     *              authGroup [raw] 控制器权限分组
     *              baseAuth [string] 控制器根权限
     *          info [raw] 详细数据
     * @title  控制器文档信息
     * @explain  根据点击侧边导航获取对应的获取API文档信息
     * @router get index-nav debug:true
     * @throws \Exception
     */
    public function getNav(Request $Request)
    {
        $input = $Request->input();
        $fatherInfo = $this->app->Route()->getNoteBlockDocument()[$input['father']]??[];
        $fatherInfo['index'] = $input['father'];
        $info = $this->app->Route()->getNoteBlockDocument()[$input['father']]['route'][$input['index']]??null;
        if(!empty($info)){
            $info['index'] = $input['index'];
        }
        return $this->succeed([
                'fatherInfo'=>$fatherInfo,
                'info'=>$info]
        ,'',200);

    }

    /**
     * @Author pizepei
     * @Created 2019/2/14 23:01
     *
     * @param \normphp\staging\Request $Request
     *      get [object] get参数
     *          father [string] 父路径
     *          index [string required] 当前路径
     *          type [string required] 参数类型
     * @return array [json]
     *      data [objectList] 数据
     *              restrain [string] 限制约束
     *              field [string] 参数名字
     *              type [string] 参数数据类型
     *              explain [string] 参数说明
     * @title  获取API的请求参数信息
     * @explain  根据点击侧边导航获取对应的获取API文档信息
     * @router get request-param
     * @throws \Exception
     */
    public function RequestParam(Request $Request)
    {
        $input = $Request->input();
        $info = $this->app->Route()->getNoteBlockDocument()[$input['father']]['route'][$input['index']]??null;
        if(!empty($info)){
            $info['index'] = $input['index'];
        }
        if(isset($info['Param']) && !empty($info['Param'])){
            $info = $info['Param'][$input['type']]['substratum']??[];
            if(!empty($info)){
                $Document = new DocumentService;
                $infoData = $Document ->getParamInit($info);
            }
        }else{
            $infoData = [];
        }
        return $this->succeed($infoData??[],'获取'.$input['index'].'成功',200);
    }
    /**
     * @Author pizepei
     * @Created 2019/2/14 23:01
     *
     * @param \normphp\staging\Request $Request
     *      get [object] get参数
     *          father [string required] 父路径
     *          index [string required] 当前路径
     *          type [string required] 参数类型
     * @return array [json]
     *      data [objectList] 数据
     *          field [required] 参数名字
     *          type [string] 参数数据类型
     *          explain [string] 参数说明
     *          restrain [string] 参数约束
     * @title  获取API的返回参数信息
     * @explain  根据点击侧边导航获取对应的获取API文档信息
     * @router get return-param debug:true
     * @throws \Exception
     */
    public function ReturnParam(Request $Request)
    {
        $input = $Request->input();
        $info = $this->app->Route()->getNoteBlockDocument()[$input['father']]['route'][$input['index']]??null;
        if($info['ReturnType'] != $input['type']){
            return $this->succeed([],'获取1'.$input['index'].'成功',200);
        }
        if(!empty($info)){
            $info['index'] = $input['index'];
        }
        if(isset($info['Return']) && !empty($info['Return'])){
            $info = $info['Return']??[];
            if(!empty($info)){
                $Document = new DocumentService;
                $infoData = $Document ->getParamInit($info);
            }
        }else{
            $info = [];
        }
        return $this->succeed($infoData??[],'获取'.$input['index'].'成功',200);
    }


    /**
     * @Author pizepei
     * @Created 2019/4/25 14:01
     * @param \normphp\staging\Request $Request
     *      get [object] get参数
     *          projectId [string] 项目id
     * @return array [json]
     *      data [raw]
     *          title [string] 标题
     * @title  获取权限树
     * @explain  根据点击侧边导航获取对应的获取API文档信息
     * @router get jurisdiction-list debug:true
     * @throws \Exception
     */
    public function jurisdictionList(Request $Request)
    {
        return $this->succeed(
            \PermissionsInfo::children
            ,'获取成功',200);
    }

    /**
     * @Author 皮泽培
     * @Created 2019/5/18 17:57
     * @param Request $Request
     *   path [object] 路径参数
     *   get [object] 路径参数
     *   post [object] post参数
     *      name [string] 姓名
     *   rule [object] 数据流参数
     * @return array [json] 定义输出返回数据
     *      id [uuid] uuid
     *      name [object] 同学名字
     * @title  IDE适配配置包
     * @explain IDE适配配置包
     * @authExtend UserExtend.list:拓展权限
     * @baseAuth Resource:public
     * @throws \Exception
     * @router post exportPhpStormSettings
     */
    public function exportPhpStormSettings(Request $Request)
    {
        if($Request->post('name') === 'settings.zip' || $Request->post('name') === 'settings')
        {
            throw new \Exception('不能为settings关键字');
        }
        $zip = new ZipArchive();
        $path = "..".DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.'PhpStormSettings'.DIRECTORY_SEPARATOR;
        $route = $path.$Request->post('name');
        $file = $path.'settings.zip';
        /**
         *
         * 怎么下载？
         */
        if ($zip->open($file) === true){

            $mcw = $zip->extractTo($route);//解压到$route这个目录中

            $zip->close();
        }
        return self::fileTemplates_includes_PHP_Function_Doc_Comment['content'];
    }
    /**
     * @Author pizepei
     * @Created 2019/4/23 23:02
     * @param \normphp\staging\Request $Request
     * @return array [json]
     * @title  框架开发文档顶部菜单
     * @explain 框架开发文档顶部菜单
     * @router get normative/new
     */
    public function messageNew(Request $Request)
    {
        header('access-Control-Allow-Origin:*');
        $data = [
            "HelloWorld"=> [
                'title'=>'Hello world',
                'route'=>[
                    'purpose'=>['title'=>'开发初衷'],
                    'character'=>['title'=>'框架特性'],
                    'standard'=>['title'=>'开发规范'],
                    'environment'=>['title'=>'开发环境'],
                    'saas'=>['title'=>'SAAS模式'],
                    'Docker'=>['title'=>'Docker支持'],
                    'production'=>['title'=>'生产环境'],
                ]
            ],
            "note"=> [
                'title'=>'注解路由',
                'route'=>[
                    'purpose'=>['title'=>'入门'],
                    'character'=>['title'=>'控制器注解'],
                    'standard'=>['title'=>'方法注解'],
                    'environment'=>['title'=>'权限注解'],
                    'saas'=>['title'=>'请求过滤'],
                    'Docker'=>['title'=>'输出过滤'],
                    'production'=>['title'=>'生产环境'],
                ]
            ],
        ];

        return $this->succeed($data,'',200);
    }



    /**
     * @Author pizepei
     * @Created 2019/4/23 23:02
     * @param \normphp\staging\Request $Request
     * @return array [json]
     * @title  框架开发文档初始化数据
     * @explain 框架开发文档初始化数据主要是返回状态码定义
     * @router get init-data
     */
    public function initData(Request $Request)
    {
        /** 数据构造*/
        $dataStructure =[
            'ErrorReturnJsonMsg'    => $this->app->__INIT__['ErrorReturnJsonMsg'],
            'ErrorReturnJsonCode'   => $this->app->__INIT__['ErrorReturnJsonCode'],
            'SuccessReturnJsonMsg'  => $this->app->__INIT__['SuccessReturnJsonMsg'],
            'SuccessReturnJsonCode' => $this->app->__INIT__['SuccessReturnJsonCode'],
            'notLoggedCode'         => $this->app->__INIT__['notLoggedCode'],
            'ReturnJsonData'        => $this->app->__INIT__['ReturnJsonData'],
            'ReturnJsonCount'       => $this->app->__INIT__['ReturnJsonCount'],
            'jurisdictionCode'      => $this->app->__INIT__['jurisdictionCode'],
        ];
        return ['dataStructure'=>$dataStructure,'nav'=>self::_NAV_];
    }


    /**
     * 文档中心菜单
     */
    const _NAV_ = <<<ABC
<li class="layui-nav-item"><a href="{{MODULE_PREFIX}}/document/document.html">API文档</a></li>
<li class="layui-nav-item"><a href="{{MODULE_PREFIX}}/document/authority.html">权限文档</a></li>
<li class="layui-nav-item"><a href="{{MODULE_PREFIX}}/document/code.html">状态码文档</a></li>
<li class="layui-nav-item"><a href="{{MODULE_PREFIX}}/document/code.html">状态码文档</a></li>
<li class="layui-nav-item">
    <a href="javascript:;">资源文档</a>
    <dl class="layui-nav-child">
        <dd><a href="">框架文档</a></dd>
        <dd><a href="">图标资源</a></dd>
        <dd><a href="">公共API</a></dd>
        <dd><a href="">授权管理</a></dd>
    </dl>
</li>
<li class="layui-nav-item">
    <a href="javascript:;">控制台</a>
    <dl class="layui-nav-child">
        <dd><a href="">初始化文件</a></dd>
        <dd><a href="">交接中心</a></dd>
        <dd><a href="">安全中心</a></dd>
    </dl>
</li>

<li class="layui-nav-item">
    <a href="javascript:;">项目管理</a>
    <dl class="layui-nav-child">
        <dd><a href="">人员管理</a></dd>
        <dd><a href="">角色管理</a></dd>
        <dd><a href="">项目配置</a></dd>
    </dl>
</li>
ABC;

}