<?php

namespace AppBundle\Utility;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Sensio\Bundle\GeneratorBundle\Generator\DoctrineEntityGenerator;
use Sensio\Bundle\GeneratorBundle\Command\Validators;


class Utility {

	private $generator;

	public function createTABLE($container,$fields,$tablename){
		return self::generateEntityAction($container,$fields,$tablename);
	}

  
    public function generateEntityAction($container,$fields,$tablename){
        $format = "annotation"; //it can also be yml/php/xml
        //$fields = "title:string(255) body:text";
        $withRepository = false; //true/false

        $entity = Validators::validateEntityName("AppBundle:".$tablename);
        list($bundle, $entity) = self::parseShortcutNotation($entity);
        $format = Validators::validateFormat($format);
        $fields = self::parseFields($fields);
        $bundle = $container->get('kernel')->getBundle($bundle);
        $generator = self::getGenerator($container);
        $generator->generate($bundle, $entity, $format, array_values($fields), $withRepository);
        return "hello";
    }

    protected function parseShortcutNotation($shortcut){
         $entity = str_replace('/', '\\', $shortcut);

         if (false === $pos = strpos($entity, ':')) {
             throw new \InvalidArgumentException(sprintf('The entity name must contain a : ("%s" given, expecting something like AcmeBlogBundle:Blog/Post)', $entity));
         }

         return array(substr($entity, 0, $pos), substr($entity, $pos + 1));
    }
    private function parseFields($input){
         if (is_array($input)) {
             return $input;
         }

         $fields = array();
         foreach (explode(' ', $input) as $value) {
             $elements = explode(':', $value);
             $name = $elements[0];
             if (strlen($name)) {
                 $type = isset($elements[1]) ? $elements[1] : 'string';
                 preg_match_all('/(.*)\((.*)\)/', $type, $matches);
                 $type = isset($matches[1][0]) ? $matches[1][0] : $type;
                 $length = isset($matches[2][0]) ? $matches[2][0] : null;

                 $fields[$name] = array('fieldName' => $name, 'type' => $type, 'length' => $length);
             }
         }
         return $fields;
    }
    protected function getGenerator($container, BundleInterface $bundle = null){
         if (null === $this->generator) {
              $this->generator = $this->createGenerator($container);
              $this->generator->setSkeletonDirs($this->getSkeletonDirs($container,$bundle));
         }

         return $this->generator;
     }

    protected function createGenerator($container){
        return new DoctrineEntityGenerator($container->get('filesystem'), $container->get('doctrine'));
    }

    
    protected function getSkeletonDirs($container, BundleInterface $bundle = null){
         $skeletonDirs = array();

         if (isset($bundle) && is_dir($dir = $bundle->getPath() . '/Resources/SensioGeneratorBundle/skeleton')) {
         $skeletonDirs[] = $dir;
         }

         if (is_dir($dir = $container->get('kernel')->getRootdir() . '/Resources/SensioGeneratorBundle/skeleton')) {
         $skeletonDirs[] = $dir;
         }

         $skeletonDirs[] = __DIR__ . '/../Resources/skeleton';
         $skeletonDirs[] = __DIR__ . '/../Resources';

         return $skeletonDirs;
     }

     
     

    
    

    
    
}