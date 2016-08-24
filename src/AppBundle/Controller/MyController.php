<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Sensio\Bundle\GeneratorBundle\Generator\DoctrineEntityGenerator;
use Sensio\Bundle\GeneratorBundle\Command\Validators;

    

class MyController extends Controller{
    private $generator;

    /**
     * @Route("/my/enerateEntity", name="my_enerate_entity")
     */
    public function generateEntityAction(){
        $format = "annotation"; //it can also be yml/php/xml
        $fields = "title:string(255) body:text category:varchar created_at:datetime";
        $withRepository = false; //true/false

        $entity = Validators::validateEntityName("AppBundle:shailendta");
        list($bundle, $entity) = $this->parseShortcutNotation($entity);
        $format = Validators::validateFormat($format);
        $fields = $this->parseFields($fields);
        $bundle = $this->get('service_container')->get('kernel')->getBundle($bundle);
        $generator = $this->getGenerator();
        $generator->generate($bundle, $entity, $format, array_values($fields), $withRepository);
    }

    protected function createGenerator(){
        return new DoctrineEntityGenerator($this->get('service_container')->get('filesystem'), $this->get('service_container')->get('doctrine'));
    }

    
    protected function getSkeletonDirs(BundleInterface $bundle = null){
         $skeletonDirs = array();

         if (isset($bundle) && is_dir($dir = $bundle->getPath() . '/Resources/SensioGeneratorBundle/skeleton')) {
         $skeletonDirs[] = $dir;
         }

         if (is_dir($dir = $this->get('service_container')->get('kernel')->getRootdir() . '/Resources/SensioGeneratorBundle/skeleton')) {
         $skeletonDirs[] = $dir;
         }

         $skeletonDirs[] = __DIR__ . '/../Resources/skeleton';
         $skeletonDirs[] = __DIR__ . '/../Resources';

         return $skeletonDirs;
     }

     
     protected function getGenerator(BundleInterface $bundle = null){
         if (null === $this->generator) {
              $this->generator = $this->createGenerator();
              $this->generator->setSkeletonDirs($this->getSkeletonDirs($bundle));
         }

         return $this->generator;
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
}
