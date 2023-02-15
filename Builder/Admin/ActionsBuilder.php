<?php

namespace Admingenerator\GeneratorBundle\Builder\Admin;

use Admingenerator\GeneratorBundle\Generator\Action;

/**
 * This builder generates php for custom actions
 * @author Piotr Gołębiewski <loostro@gmail.com>
 */
class ActionsBuilder extends BaseBuilder
{
    protected ?array $batchActions = null;

    public function getYamlKey(): string
    {
        return 'actions';
    }

    public function getVariables(): array
    {
        // If credentials are not globally defined,
        // check if an action have credentials
        if (null === $this->getVariable('credentials')) {
            $this->variables['credentials'] = false;
            foreach (array_merge(array_values($this->getObjectActions()), array_values($this->getBatchActions())) as $action) {
                if ($action->getCredentials()) {
                    $this->variables['credentials'] = true;
                    break;
                }
            }
        }

        return parent::getVariables();
    }

    /**
     * Return a list of batch action from list.batch_actions
     */
    public function getBatchActions(): array
    {
        if (null === $this->batchActions) {
            $this->batchActions = [];
            $this->findBatchActions();
        }

        return $this->batchActions;
    }

    protected function setUserBatchActionConfiguration(Action $action)
    {
        $batchActions = $this->getVariable('batch_actions', array());
        $builderOptions = is_array($batchActions) && array_key_exists($action->getName(), $batchActions)
            ? $batchActions[$action->getName()]
            : array();

        $globalOptions = $this->getGenerator()->getFromYaml(
            'params.batch_actions.'.$action->getName(), array()
        );

        if (null !== $builderOptions) {
            foreach ($builderOptions as $option => $value) {
                $action->setProperty($option, $value);
            }
        } elseif (null !== $globalOptions) {
            foreach ($globalOptions as $option => $value) {
                $action->setProperty($option, $value);
            }
        }
    }

    protected function addBatchAction(Action $action)
    {
        $this->batchActions[$action->getName()] = $action;
    }

    protected function findBatchActions()
    {
        $batchActions = $this->getVariable('batch_actions', array());

        foreach ($batchActions as $actionName => $actionParams) {
            $action = $this->findBatchAction($actionName);
            if (!$action) {
                $action = new Action($actionName);
            }

            if ($globalCredentials = $this->getGenerator()->getFromYaml('params.credentials')) {
                // If generator is globally protected by credentials
                // batch actions are also protected
                $action->setCredentials($globalCredentials);
            }

            $this->setUserBatchActionConfiguration($action);
            $this->addBatchAction($action);
        }
    }
}
