<?php
// don't mind my comment just practicing lol :(-Mbognan
declare(strict_types=1);
namespace BuyMeACoffee\Kernel\Http;

use InvalidArgumentException;
use ReflectionException;  
use ReflectionClass;
use ReflectionMethod;

class Router{
  private const CONTROLLER_NAMESPACE = 'BuyMeACoffee\Controller\\';
  // private const URI_REGEX = "#^$uri$#";
  private const CONTROLLER_SEPARATOR = '@';
  public const METHOD_GET = 'GET';
  public const METHOD_POST = 'POST';

  private static ?string $httpMethod;

  public static function get(string $uri, string $classMethod=''){
    self::$httpMethod = 'GET';
    self::execute($uri, $classMethod);
  }

  public static function post(string $uri, string $classMethod=''){
    self::$httpMethod = 'POST';
    self::execute($uri, $classMethod);
  }
  
  public static function execute(string $uri , string $method=''){
    $uri = '/' . trim($uri,'/');

    $url = isset($_GET['uri']) ? '/' . $_GET['uri']: '/';

    if(preg_match("#^$uri$#", $url, $params)){
      if(self::isRedirection($method)){
        header(
          sprintf('Location: %s%s',$_ENV['SITE_URL'], $method)
          );
      } else if(self::isHttpMethodValid()){
                
                $split = explode(self::CONTROLLER_SEPARATOR, $method);
                $className = self::CONTROLLER_NAMESPACE . $split[0];
                $method = $split[1];
                try {
                  $reflection = new ReflectionClass($className);
                  //check the class if it has a method
                  if(class_exists($className) && $reflection->hasMethod($method)){
                      $action = new ReflectionMethod($className, $method);
                      if($action->isPublic()){
                        //perform Controller action
                        return $action->invokeArgs(new $className, self::getActionParameters($params));
                      }
                  }
                } catch (ReflectionException $err) {
                  echo $err->getMessage();
                  
                

              }

          }
      }else{
        throw new InvalidArgumentException(sprintf('Invalid "%s" HTTP Request',$_SERVER['REQUEST_METHOD']));
      }
  
          //Not Found here

      
  }

  private static function isHttpMethodValid():bool{
    return self::$httpMethod !== null && $_SERVER['REQUEST_METHOD'] === self::$httpMethod;
  }
  private static function getActionParameters(array $params):array{
      foreach($params as $key => $value){
        $params[$key] = str_replace('/','',$params);
      }
      return $params;
  }
  private static function isRedirection(string  $method):bool{

    return !str_contains($method, self::CONTROLLER_SEPARATOR)!==false;
  }
}