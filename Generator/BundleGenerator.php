<?php

namespace Admingenerator\GeneratorBundle\Generator;

use Sensio\Bundle\GeneratorBundle\Generator\Generator as BaseBundleGenerator;
use Sensio\Bundle\GeneratorBundle\Model\Bundle;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Generates an admin bundle.
 *
 * @author Cedric LOMBARDOT
 */
class BundleGenerator extends BaseBundleGenerator
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var string
     */
    private $skeletonDir;

    /**
     * @var string
     */
    protected $generator;

    /**
     * @var string
     */
    protected $prefix;

    /**
     * @var array
     */
    protected $actions = array(
        'New'  => array('views' => array(
            'index',
            'form',
        )),
        'List' => array('views' => array(
            'index',
            'results',
            'filters',
            'row'
        )),
        'Excel' => array('views' => array()),
        'Edit' => array('views' => array(
            'index',
            'form',
        )),
        'Show' => array('views' => array('index')),
        'Actions' => array('views' => array('index'))
    );

    /**
     * @var array
     */
    protected $forms = array('New', 'Filters', 'Edit');

    /**
     * @param string $skeletonDir
     */
    public function __construct(Filesystem $filesystem, $skeletonDir)
    {
        $this->filesystem = $filesystem;
        $this->skeletonDir = $skeletonDir;
        if (method_exists($this, 'setSkeletonDirs')) {
            $this->setSkeletonDirs($this->skeletonDir);
        }
    }

    /**
     * @param string $generator
     */
    public function setGenerator($generator)
    {
        $this->generator = $generator;
    }

    /**
     * @param string $prefix
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * @param Bundle $bundle
     * @param string $modelName
     */
    public function generate(Bundle $bundle, $modelName, $formsOnly)
    {
        $dir = $bundle->getTargetDirectory();

        // Retrieves model folder depending on chosen Model Manager
        $modelFolder = '';
        switch ($this->generator) {
            case 'propel':
                $modelFolder = 'Model';
                break;
            case 'doctrine':
                $modelFolder = 'Entity';
                break;
            case 'doctrine_orm':
                $modelFolder = 'Document';
                break;
        }

        if (false === strpos($bundle->getNamespace(), '\\')) {
            $bundleName = $bundle->getNamespace();
            $namespacePrefix = null;
        } else {
            list( $namespacePrefix, $bundleName) = explode('\\', $bundle->getNamespace(), 2);
        }

        $parameters = array(
            'namespace'        => $bundle->getNamespace(),
            'bundle'           => $bundle->getName(),
            'generator'        => 'admingenerator.generator.'.$this->generator,
            'namespace_prefix' => $namespacePrefix,
            'bundle_name'      => $bundleName,
            'model_folder'     => $modelFolder,
            'model_name'       => $modelName,
            'prefix'           => ucfirst($this->prefix),
            'forms_only'       => $formsOnly
        );

        if (!file_exists($dir.'/'.$bundle->getName().'.php')) {
            $this->renderGeneratedFile('Bundle.php.twig', $dir.'/'.$bundle->getName().'.php', $parameters);
        }

        foreach ($this->forms as $form) {
            $parameters['form'] = $form;

            $formFile = $dir.'/Form/Type/'.($this->prefix ? ucfirst($this->prefix).'/' : '').$form.'Type.php';
            $this->copyPreviousFile($formFile);
            $this->renderGeneratedFile(
                'DefaultType.php.twig',
                $formFile,
                $parameters
            );
        }

        $optionsFile = $dir.'/Form/Type/'.($this->prefix ? ucfirst($this->prefix).'/' : '').'Options.php';
        $this->copyPreviousFile($optionsFile);
        $this->renderGeneratedFile(
            'DefaultOptions.php.twig',
            $optionsFile,
            $parameters
        );

        $generatorFile = $dir.'/Resources/config/'.($this->prefix ? ucfirst($this->prefix).'-' : '').'generator.yml';
        $this->copyPreviousFile($generatorFile);
        $this->renderGeneratedFile(
            'generator.yml.twig',
            $generatorFile,
            $parameters
        );

        if ($formsOnly) {
            return;
        }

        foreach ($this->actions as $action => $actionProperties) {
            $parameters['action'] = $action;

            $controllerFile = $dir.'/Controller/'
                .($this->prefix ? ucfirst($this->prefix).'/' : '').$action.'Controller.php';
            $this->copyPreviousFile($controllerFile);
            $this->renderGeneratedFile(
                'DefaultController.php.twig',
                $controllerFile,
                $parameters
            );

            foreach ($actionProperties['views'] as $templateName) {
                $templateFile = $dir.'/Resources/views/'.ucfirst($this->prefix).$action.'/'.$templateName.'.html.twig';
                $this->copyPreviousFile($templateFile);
                $this->renderGeneratedFile(
                    'default_view.html.twig',
                    $templateFile,
                    $parameters + array('view' => $templateName)
                );
            }
        }
    }

    /**
     * @param string $template
     * @param string $target
     */
    protected function renderGeneratedFile($template, $target, array $parameters)
    {
        $this->renderFile($template, $target, $parameters);
    }

    /**
     * @param string $oldname
     */
    protected function copyPreviousFile($oldname)
    {
        if (file_exists($oldname)) {
            $newname = $oldname.'~';

            // Find unused copy name
            if (file_exists($newname)) {
                $key = 0;
                do {
                    $key++;
                } while (file_exists($oldname.'~'.$key));

                $newname = $oldname.'~'.$key;
            }

            // Create new copy
            rename($oldname, $newname);
        }
    }
}
