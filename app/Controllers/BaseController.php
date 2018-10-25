<?php
namespace App\Controllers;
use App\Models\User;
use App\Models\Event;
use App\Models\Category;
use App\Models\Setting;
use Psr\Http\Message\ResponseInterface;
use \Slim\Container;
 
class BaseController
{

    /**
     * Slim DI Container
     *
     * @var \Slim\Container
     */
    protected $container;
	protected $data = array(
        'title' => '',
        'description' => '',
        'keywords' => '',
        'h1' => '',
        'flash' => array(),
        'page_counts' => [5, 10, 15, 25, 50, 100, 150],
    );
    /**
     * Construtor
     *
     * @param object $container
     * @return void
     */
    public function __construct(Container $container)
    {
		
        $this->container = $container;
		$this->init();
    }
    
    /**
     * Pega uma variável $_GET definda em request
     *
     * @param string $key
     * @return string|null
     */
    public function httpGet($key)
    {
        if (isset($this->container->request->getQueryParams()[$key])) {
            return $this->container->request->getQueryParams()[$key];
        }
		
        return null;
    }
	
	/**
     * @param ResponseInterface $response
     * @param string $template
     * @param array $data
     */
    public function render(
        ResponseInterface $response,
        $template,
        array $data = []
    ) {
        $this->container->view->render($response, $template, $data);
    }
    /**
     * @param string $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        return $this->container->get($name);
    }

    /**
     * Pega uma variável $_POST definda em request
     *
     * @param string $key
     * @return string|null
     */
    public function httpPost($key)
    {
        if (isset($this->container->request->getParsedBody()[$key])) {
            return $this->container->request->getParsedBody()[$key];
        }
		
        return null;
    }
    
    /**
     * Transforma um objeto em uma string no formato json
     *
     * @param object $data
     * @return string
     * @throws \Exception Quando $data não é um objeto
     */
    public static function encode($data)
    {
        if (!is_object($data)) {
            throw new \Exception('$data deve ser um objeto.');
        }
		
        return json_encode($data, JSON_PRETTY_PRINT) . PHP_EOL;
    }
    
    /**
     * Cria um objeto de erro em string no formato json
     *
     * @param string $code
     * @param string $path
     * @param string $status
     * @param string $extra
     * @return string
     */
    public static function error($code, $path, $status, $extra = '')
    {
        $error = new \StdClass;
        
        $error->error = [
            'code' => $code,
            'path' => $path,
            'status' => $status
        ];
        
        if ($extra) {
            $error->error['extra'] = $extra;
        }

        return self::encode($error);
    }
    
    /**
     * Cria um objeto de resource em string no formato json
     *
     * @param string $request
     * @param string $path
     * @return string
     */
    public function resource($path)
    {
        $uri = $this->container->request->getUri();

        $scheme = $uri->getScheme();
        $host = $uri->getHost();
        $port = $uri->getPort();
        
        $location = $scheme . '://' . $host . ($port ? ':' . $port : null) . '/' . $path;
        
        $resource = new \StdClass;
        
        $resource->resource = [
            'location' => $location
        ];

        return self::encode($resource);
    }
    
    /**
     * Checa uma lista de expressões booleanas
     *
     * @param array $validations
     * @return bool
     * @throws \Exception Quando $validations não é um array
     */
    public static function validate($validations)
    {
        if (!is_array($validations)) {
            throw new \Exception('$validations deve ser um array de valores booleanos.');
        }
		
		foreach ($validations as $v) {
			if ($v === false) {
				return false;
			}
		}
        
        return true;
    }
	
	//Intiating the base controller class //
	function init(){
	    
	    $this->data['setting'] =  Setting::first();
        $this->data['leftMenuCateogories'] =  Category::orderBy('id')->get();
		$this->data['categoryURL'] =  CATEGORY_WEB_PATH.'/';
		$this->data['eventURL'] =  EVENT_WEB_PATH.'/';
		$this->data['currentYear'] =  date('Y');
		$this->data['currentMonth'] =  date('m');
        $this->data['admin_email'] =  'gutropolis@gmail.com'; 
        $this->data['metaTitle'] = 'Cultur Access';
        $this->data['metaDescription'] = 'Cultur Access';
		if(isMemberLogin()){
			$this->data['is_login'] = '1'; 
		}else{
			$this->data['is_login'] = '0'; 
		}
		 
	}
}
