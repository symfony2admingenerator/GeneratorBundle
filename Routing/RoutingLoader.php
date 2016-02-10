<?php

namespace Admingenerator\GeneratorBundle\Routing;

use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

class RoutingLoader extends FileLoader
{
    // Assoc beetween a controller and its route path
    //@todo make an object for this
    protected $actions = array(
        'list' => array(
            'path'         => '/',
            'defaults'     => array(),
            'requirements' => array(),
            'methods'      => array('GET'),
        ),
        'excel'=> array(
            'path'         => '/excel',
            'defaults'     => array(),
            'requirements' => array(),
            'methods'      => array('GET'),
            'controller'   => 'excel',
        ),
        'edit' => array(
            'path'         => '/{pk}/edit',
            'defaults'     => array(),
            'requirements' => array(),
            'methods'      => array('GET'),
        ),
        'update' => array(
            'path'         => '/{pk}/update',
            'defaults'     => array(),
            'requirements' => array(),
            'methods'      => array('POST'),
            'controller'   => 'edit',
        ),
        'show' => array(
            'path'         => '/{pk}/show',
            'defaults'     => array(),
            'requirements' => array(),
            'methods'      => array('GET'),
        ),
        'object' => array(
            'path'         => '/{pk}/{action}',
            'defaults'     => array(),
            'requirements' => array(),
            'methods'      => array('GET', 'POST'),
            'controller'   => 'actions',
        ),
        'batch' => array(
            'path'         => '/batch',
            'defaults'     => array(),
            'requirements' => array(),
            'methods'      => array('POST'),
            'controller'   => 'actions',
        ),
        'new' => array(
            'path'         => '/new',
            'defaults'     => array(),
            'requirements' => array(),
            'methods'      => array('GET'),
        ),
        'create' => array(
            'path'         => '/create',
            'defaults'     => array(),
            'requirements' => array(),
            'methods'      => array('POST'),
            'controller'   => 'new',
        ),
        'filters' => array(
            'path'         => '/filter',
            'defaults'     => array(),
            'requirements' => array(),
            'methods'      => array('POST', 'GET'),
            'controller'   => 'list',
        ),
        'scopes' => array(
            'path'         => '/scope/{group}/{scope}',
            'defaults'     => array(),
            'requirements' => array(),
            'methods'      => array('POST', 'GET'),
            'controller'   => 'list',
        ),
    );

    /**
     * @var array
     */
    protected $yaml = array();

    public function load($resource, $type = null)
    {
        $collection = new RouteCollection();

        $resource = str_replace('\\', '/', $resource);
        $this->yaml = Yaml::parse(file_get_contents($this->getGeneratorFilePath($resource)));

        $namespace = $this->getNamespaceFromResource($resource);
        $bundle_name = $this->getBundleNameFromResource($resource);

        foreach ($this->actions as $controller => $datas) {
            $action = 'index';

            $loweredNamespace = str_replace(array('/', '\\'), '_', $namespace);
            if ($controller_folder = $this->getControllerFolder($resource)) {
                $route_name = $loweredNamespace . '_' . $bundle_name . '_' . $controller_folder . '_' . $controller;
            } else {
                $route_name = $loweredNamespace . '_' . $bundle_name . '_' . $controller;
            }

            if (in_array($controller, array('edit', 'update', 'object', 'show')) &&
                null !== $pk_requirement = $this->getFromYaml('params.pk_requirement', null)) {
                $datas['requirements'] = array_merge(
                    $datas['requirements'],
                    array('pk' => $pk_requirement)
                );
            }

            if (isset($datas['controller'])) {
                $action     = $controller;
                $controller = $datas['controller'];
            }

            $controllerName = $resource.ucfirst($controller).'Controller.php';
            if (!is_file($controllerName)) {
                // TODO: what does it mean if controller is not a file??
                continue;
            }

            if ($controller_folder) {
                $datas['defaults']['_controller'] = $namespace . '\\'
                        . $bundle_name . '\\Controller\\'
                        . $controller_folder . '\\'
                        . ucfirst($controller) . 'Controller::'
                        . $action . 'Action';
            } else {
                $datas['defaults']['_controller'] = $loweredNamespace
                        . $bundle_name . ':'
                        . ucfirst($controller) . ':' . $action;
            }

            $route = new Route($datas['path'], $datas['defaults'], $datas['requirements']);
            $route->setMethods($datas['methods']);

            $route_name = ltrim($route_name, '_'); // fix routes in AppBundle without vendor

            $collection->add($route_name, $route);
            $collection->addResource(new FileResource($controllerName));
        }

        return $collection;
    }

    public function supports($resource, $type = null)
    {
        return 'admingenerator' == $type;
    }

    /**
     * @return string
     */
    protected function getControllerFolder($resource)
    {
        preg_match('#.+/.+Bundle/Controller?/(.*?)/?$#', $resource, $matches);

        return $matches[1];
    }

    /**
     * @return string
     */
    protected function getBundleNameFromResource($resource)
    {
        preg_match('#.+/(.+Bundle)/Controller?/(.*?)/?$#', $resource, $matches);

        return $matches[1];
    }

    protected function getNamespaceFromResource($resource)
    {
        $finder = Finder::create()
            ->name('*Bundle.php')
            ->depth(0)
            ->in(realpath($resource.'/../../')) // ressource is controller folder
            ->getIterator();

        foreach ($finder as $file) {
            preg_match('/namespace (.+);/', file_get_contents($file->getRealPath()), $matches);

            return implode('\\', explode('\\', $matches[1], -1)); // Remove the short bundle name
        }

        throw new \Exception(sprintf('Bundle file not found in %s.', realpath($resource.'/../../')));
    }

    /**
     * @return string
     */
    protected function getGeneratorFilePath($resource)
    {
        // TODO: use the GeneratorsFinder
        // Find the *-generator.yml
        $finder = Finder::create()
            ->name($this->getControllerFolder($resource).'-generator.yml')
            ->in(realpath($resource.'/../../Resources/config/'))
            ->getIterator();

        foreach ($finder as $file) {
            return $file->getRealPath();
        }

        throw new \Exception(sprintf(
            'Generator file for %s not found in %s',
            $this->getControllerFolder($resource),
            realpath($resource.'/../../Resources/config/')
        ));
    }

    /**
     * @param string $yaml_path string with point for levels
     */
    protected function getFromYaml($yaml_path, $default = null)
    {
        $search_in = $this->yaml;
        $yaml_path = explode('.', $yaml_path);
        foreach ($yaml_path as $key) {
            if (!isset($search_in[$key])) {
                return $default;
            }
            $search_in = $search_in[$key];
        }

        return $search_in;
    }
}
