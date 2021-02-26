<?php
/**
 * Class Deploy
 * @title 閮ㄧ讲鍩虹绫�
 */
namespace normphpCore\devDoc\controller;
use authority\app\Resource;
use normphp\staging\Controller;
use normphp\staging\Request;
use normphp\staging\service\DocumentService;
use ZipArchive;

class BasicsDocument extends Controller
{
    /**
     * 鍩虹鎺у埗鍣ㄤ俊鎭�
     */
    const CONTROLLER_INFO = [
        'User'=>'pizepei',
        'title'=>'鏂囨。鎺у埗鍣�',//鎺у埗鍣ㄦ爣棰�
        'namespace'=>'',//闂ㄩ潰鎺у埗鍣ㄥ懡鍚嶇┖闂�
        'basePath'=>'/document/',//鍩虹璺敱
    ];

    protected $path  = '';
    /**
     * @param \normphp\staging\Request $Request
     *      path [object] 璺緞鍙傛暟
     *          type [string] 璺緞
     * @return array [html]
     * @title  鏂囨。鍏ュ彛锛堝紑鍙戝姪鎵嬶級
     * @explain 鏂囨。鍏ュ彛锛圓PI鏂囨。銆佹潈闄愭枃妗ｃ�佸叕鍏辫祫婧愭枃妗ｏ級
     * @router get index/:type[string].html debug:true
     * @throws \Exception
     */
    public function index(Request $Request)
    {
        $name = $Request->path('type')==='index'?'document':$Request->path('type');
        $data = [
            '_NAV_'=>self::_NAV_,
            'layui.css'=>'https://www.layuicdn.com/layui-v2.5.5/css/layui.css',
            'layui.js'=>'https://www.layuicdn.com/layui-v2.5.5/layui.js',
            'local.layui.css'=>\Deploy::VIEW_RESOURCE_PREFIX.'/start/layui/css/layui.css',
            'local.layui.js'=>\Deploy::VIEW_RESOURCE_PREFIX.'/start/layui/layui.js',
            'VIEW_RESOURCE_PREFIX'=>\Deploy::VIEW_RESOURCE_PREFIX,
            'MODULE_PREFIX'=>\Deploy::MODULE_PREFIX===''?'':'/'.\Deploy::MODULE_PREFIX,
            'jsonDataName'=>$this->app->__INIT__['ReturnJsonData'],
        ];
        $path = dirname(__DIR__).DIRECTORY_SEPARATOR.'template'.DIRECTORY_SEPARATOR.'document'.DIRECTORY_SEPARATOR;
        return $this->view($name,$data,$path,'html',false);
    }
    /**
     * @param \normphp\staging\Request $Request html
     *      path [object] 璺緞鍙傛暟
     *          name [string required] 鎵╁睍鏂囦欢鍚嶇О
     * @return array [js]
     * @title  鏂囨。layui Extends
     * @explain  鏂囨。layui Extends
     * @router get layui/extends/:name[string].js debug:true
     * @throws \Exception
     */
    public function layuiExtends(Request $Request)
    {
        $path = dirname(__DIR__).DIRECTORY_SEPARATOR.'template'.DIRECTORY_SEPARATOR.'document'.DIRECTORY_SEPARATOR;
        return $this->view($Request->path('name'),[],$path,'js',false);

    }

    /**
     * @return array [json]
     *      data [raw] 鑿滃崟鏁版嵁
     * @title  API鏂囨。 渚ц竟瀵艰埅
     * @explain  渚ц竟瀵艰埅
     * @router get nav-list debug:true
     * @throws \Exception
     */
    public function navList()
    {
        return $this->succeed($this->app->Route()->noteBlock);
    }
    /**
     * @Author pizepei
     * @Created 2019/2/12 23:01
     *
     * @param \normphp\staging\Request $Request
     *      get [object] 璺緞鍙傛暟
     *          father [string] 鐖惰矾寰�
     *          index [string] 褰撳墠璺緞
     * @return array [json]
     *      data [object] 鎺у埗鍣ㄦ暟鎹�
     *          fatherInfo [object] 鎺у埗鍣ㄦ暟鎹�
     *              baseAuthGroup [raw] 鍩虹鏉冮檺
     *              route [raw] 璺敱璇︾粏
     *              index [string] 鍛藉悕绌洪棿
     *              title [string] 鎺у埗鍣ㄦ爣棰�
     *              class [string] 鎺у埗鍣ㄥ悕
     *              User [string] 鍒涘缓鑰�
     *              basePath [string] 鎺у埗鍣ㄦ牴璺敱
     *              authGroup [raw] 鎺у埗鍣ㄦ潈闄愬垎缁�
     *              baseAuth [string] 鎺у埗鍣ㄦ牴鏉冮檺
     *          info [raw] 璇︾粏鏁版嵁
     * @title  鎺у埗鍣ㄦ枃妗ｄ俊鎭�
     * @explain  鏍规嵁鐐瑰嚮渚ц竟瀵艰埅鑾峰彇瀵瑰簲鐨勮幏鍙朅PI鏂囨。淇℃伅
     * @router get index-nav debug:true
     * @throws \Exception
     */
    public function getNav(Request $Request)
    {
        $input = $Request->input();
        $fatherInfo = $this->app->Route()->noteBlock[$input['father']]??[];
        $fatherInfo['index'] = $input['father'];
        $info = $this->app->Route()->noteBlock[$input['father']]['route'][$input['index']]??null;
        if(!empty($info)){
            $info['index'] = $input['index'];
        }
        return $this->succeed([
                'fatherInfo'=>$fatherInfo,
                'info'=>$info
            ]
        );

    }

    /**
     * @Author pizepei
     * @Created 2019/2/14 23:01
     *
     * @param \normphp\staging\Request $Request
     *      get [object] get鍙傛暟
     *          father [string] 鐖惰矾寰�
     *          index [string required] 褰撳墠璺緞
     *          type [string required] 鍙傛暟绫诲瀷
     * @return array [json]
     *      data [objectList] 鏁版嵁e
     *              field [string] 鍙傛暟鍚嶅瓧
     *              type [string] 鍙傛暟鏁版嵁绫诲瀷
     *              explain [string] 鍙傛暟璇存槑
     * @title  鑾峰彇API鐨勮姹傚弬鏁颁俊鎭�
     * @explain  鏍规嵁鐐瑰嚮渚ц竟瀵艰埅鑾峰彇瀵瑰簲鐨勮幏鍙朅PI鏂囨。淇℃伅
     * @router get request-param
     * @throws \Exception
     */
    public function RequestParam(Request $Request)
    {
        $input = $Request->input();
        $info = $this->app->Route()->noteBlock[$input['father']]['route'][$input['index']]??null;
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
        return $this->succeed($infoData??[],'鑾峰彇'.$input['index'].'鎴愬姛',0);
    }

    /**
     * @Author pizepei
     * @Created 2019/2/14 23:01
     *
     * @param \normphp\staging\Request $Request
     *      get [object] get鍙傛暟
     *          father [string required] 鐖惰矾寰�
     *          index [string required] 褰撳墠璺緞
     *          type [string required] 鍙傛暟绫诲瀷
     * @return array [json]
     *      data [objectList] 鏁版嵁
     *          field [string] 鍙傛暟鍚嶅瓧
     *          type [string] 鍙傛暟鏁版嵁绫诲瀷
     *          explain [string] 鍙傛暟璇存槑
     *          restrain [string] 鍙傛暟绾︽潫
     * @title  鑾峰彇API鐨勮繑鍥炲弬鏁颁俊鎭�
     * @explain  鏍规嵁鐐瑰嚮渚ц竟瀵艰埅鑾峰彇瀵瑰簲鐨勮幏鍙朅PI鏂囨。淇℃伅
     * @router get return-param debug:true
     * @throws \Exception
     */
    public function ReturnParam(Request $Request)
    {
        $input = $Request->input();
        $info = $this->app->Route()->noteBlock[$input['father']]['route'][$input['index']]??null;
        if($info['ReturnType'] != $input['type']){
            return $this->succeed([],'鑾峰彇1'.$input['index'].'鎴愬姛',0);
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
        return $this->succeed($infoData??[],'鑾峰彇'.$input['index'].'鎴愬姛',0);
    }


    /**
     * @Author pizepei
     * @Created 2019/4/25 14:01
     * @param \normphp\staging\Request $Request
     *      get [object] get鍙傛暟
     *          projectId [string] 椤圭洰id
     * @return array [json]
     *      data [raw]
     *          title [string] 鏍囬
     * @title  鑾峰彇鏉冮檺鏍�
     * @explain  鏍规嵁鐐瑰嚮渚ц竟瀵艰埅鑾峰彇瀵瑰簲鐨勮幏鍙朅PI鏂囨。淇℃伅
     * @router get jurisdiction-list debug:true
     * @throws \Exception
     */
    public function jurisdictionList(Request $Request)
    {
        return $this->succeed(
            \PermissionsInfo::children
            ,'鑾峰彇鎴愬姛');
    }

    /**
     * @Author 鐨辰鍩�
     * @Created 2019/5/18 17:57
     * @param Request $Request
     *   path [object] 璺緞鍙傛暟
     *   get [object] 璺緞鍙傛暟
     *   post [object] post鍙傛暟
     *      name [string] 濮撳悕
     *   rule [object] 鏁版嵁娴佸弬鏁�
     * @return array [json] 瀹氫箟杈撳嚭杩斿洖鏁版嵁
     *      id [uuid] uuid
     *      name [object] 鍚屽鍚嶅瓧
     * @title  IDE閫傞厤閰嶇疆鍖�
     * @explain IDE閫傞厤閰嶇疆鍖�
     * @authExtend UserExtend.list:鎷撳睍鏉冮檺
     * @baseAuth Resource:public
     * @throws \Exception
     * @router post exportPhpStormSettings
     */
    public function exportPhpStormSettings(Request $Request)
    {
        if($Request->post('name') === 'settings.zip' || $Request->post('name') === 'settings')
        {
            throw new \Exception('涓嶈兘涓簊ettings鍏抽敭瀛�');
        }
        $zip = new ZipArchive();
        $path = "..".DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.'PhpStormSettings'.DIRECTORY_SEPARATOR;
        $route = $path.$Request->post('name');
        $file = $path.'settings.zip';
        /**
         *
         * 鎬庝箞涓嬭浇锛�
         */
        if ($zip->open($file) === true){

            $mcw = $zip->extractTo($route);//瑙ｅ帇鍒�$route杩欎釜鐩綍涓�

            $zip->close();
        }
        return self::fileTemplates_includes_PHP_Function_Doc_Comment['content'];
    }
    /**
     * @Author pizepei
     * @Created 2019/4/23 23:02
     * @param \normphp\staging\Request $Request
     * @return array [json]
     * @title  妗嗘灦寮�鍙戞枃妗ｉ《閮ㄨ彍鍗�
     * @explain 妗嗘灦寮�鍙戞枃妗ｉ《閮ㄨ彍鍗�
     * @router get normative/new
     */
    public function messageNew(Request $Request)
    {
        header('access-Control-Allow-Origin:*');
        $data = [
            "HelloWorld"=> [
                'title'=>'Hello world',
                'route'=>[
                    'purpose'=>['title'=>'寮�鍙戝垵琛�'],
                    'character'=>['title'=>'妗嗘灦鐗规��'],
                    'standard'=>['title'=>'寮�鍙戣鑼�'],
                    'environment'=>['title'=>'寮�鍙戠幆澧�'],
                    'saas'=>['title'=>'SAAS妯″紡'],
                    'Docker'=>['title'=>'Docker鏀寔'],
                    'production'=>['title'=>'鐢熶骇鐜'],
                ]
            ],
            "note"=> [
                'title'=>'娉ㄨВ璺敱',
                'route'=>[
                    'purpose'=>['title'=>'鍏ラ棬'],
                    'character'=>['title'=>'鎺у埗鍣ㄦ敞瑙�'],
                    'standard'=>['title'=>'鏂规硶娉ㄨВ'],
                    'environment'=>['title'=>'鏉冮檺娉ㄨВ'],
                    'saas'=>['title'=>'璇锋眰杩囨护'],
                    'Docker'=>['title'=>'杈撳嚭杩囨护'],
                    'production'=>['title'=>'鐢熶骇鐜'],
                ]
            ],
        ];

        return $this->succeed($data);
    }



    /**
     * @Author pizepei
     * @Created 2019/4/23 23:02
     * @param \normphp\staging\Request $Request
     * @return array [json]
     * @title  妗嗘灦寮�鍙戞枃妗ｅ垵濮嬪寲鏁版嵁
     * @explain 妗嗘灦寮�鍙戞枃妗ｅ垵濮嬪寲鏁版嵁涓昏鏄繑鍥炵姸鎬佺爜瀹氫箟
     * @router get init-data
     */
    public function initData(Request $Request)
    {
        /** 鏁版嵁鏋勯��*/
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
     * 鏂囨。涓績鑿滃崟
     */
    const _NAV_ = <<<ABC
<li class="layui-nav-item"><a href="{{MODULE_PREFIX}}/document/index/document.html">API鏂囨。</a></li>
<li class="layui-nav-item"><a href="{{MODULE_PREFIX}}/document/index/authority.html">鏉冮檺鏂囨。</a></li>
<li class="layui-nav-item"><a href="{{MODULE_PREFIX}}/document/index/code.html">鐘舵�佺爜鏂囨。</a></li>
<li class="layui-nav-item"><a href="{{MODULE_PREFIX}}/document/index/code.html">鐘舵�佺爜鏂囨。</a></li>
<li class="layui-nav-item">
    <a href="javascript:;">璧勬簮鏂囨。</a>
    <dl class="layui-nav-child">
        <dd><a href="">妗嗘灦鏂囨。</a></dd>
        <dd><a href="">鍥炬爣璧勬簮</a></dd>
        <dd><a href="">鍏叡API</a></dd>
        <dd><a href="">鎺堟潈绠＄悊</a></dd>
    </dl>
</li>
<li class="layui-nav-item">
    <a href="javascript:;">鎺у埗鍙�</a>
    <dl class="layui-nav-child">
        <dd><a href="">鍒濆鍖栨枃浠�</a></dd>
        <dd><a href="">浜ゆ帴涓績</a></dd>
        <dd><a href="">瀹夊叏涓績</a></dd>
    </dl>
</li>

<li class="layui-nav-item">
    <a href="javascript:;">椤圭洰绠＄悊</a>
    <dl class="layui-nav-child">
        <dd><a href="">浜哄憳绠＄悊</a></dd>
        <dd><a href="">瑙掕壊绠＄悊</a></dd>
        <dd><a href="">椤圭洰閰嶇疆</a></dd>
    </dl>
</li>
ABC;

}