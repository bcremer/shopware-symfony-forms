<?php
/**
 * @author Martin Schindler
 * @copyright dasistweb GmbH (http://www.dasistweb.de)
 * Date: 17.11.15
 * Time: 12:24
 */

namespace ShopwareSymfonyForms\FormFactory;

use ShopwareSymfonyForms\FormFactory\Manager\ExtendedEntityManager;
use ShopwareSymfonyForms\FormFactory\Parser\TemplateNameParser;
use ShopwareSymfonyForms\FormFactory\Plugins\SmartyPlugins;
use Symfony\Bridge\Doctrine\Form\DoctrineOrmExtension;
use Symfony\Bundle\FrameworkBundle\Templating\Helper\FormHelper;
use Symfony\Bundle\FrameworkBundle\Templating\Helper\TranslatorHelper;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\Extension\Templating\TemplatingRendererEngine;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormRenderer;
use Symfony\Component\Form\Forms;
use Symfony\Component\Templating\Loader\FilesystemLoader;
use Symfony\Component\Templating\PhpEngine;
use Symfony\Component\Translation\Loader\XliffFileLoader;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Validator\Validation;

/**
 * Class FormFactory
 * @author Martin Schindler
 * @package ShopwareSymfonyForms\FormFactory
 */
class FormFactory implements FormFactoryInterface
{

    /** Path to smarty views */
    const VIEW_PATH = __DIR__ . '/Resources/views/Form';

    /** @var FormFactoryInterface $formFactory */
    protected $formFactory;

    /** @var string $symfonyVendorDir */
    protected $symfonyVendorDir = __DIR__ . "/../../../../../symfony";

    /** @var null|FormHelper $formHelper */
    protected $formHelper = null;

    /** @var string $defaultLocale */
    protected $defaultLocale = "en";

    /**
     * FormFactory constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        # configuring the form factory
        $this->formFactory = Forms::createFormFactoryBuilder()
            ->addExtension(new DoctrineOrmExtension(new ExtendedEntityManager($container->get('models'))))
            ->addExtension(new ValidatorExtension(Validation::createValidator()))
            ->getFormFactory();

        # registering all necessary smarty plugins
        new SmartyPlugins($this->getFormHelper(), $container->get('template'));
    }

    /**
     * Get list of paths to the symfony form themes
     * @author Martin Schindler
     * @return array
     */
    private function getFormThemePaths()
    {
        $formDir = $this->symfonyVendorDir . '/framework-bundle/Resources/views/Form';
        $defaultThemes = array(
            $formDir,
            self::VIEW_PATH
        );

        return $defaultThemes;
    }

    /**
     * Wrapping the original form factory method
     * Get the form helper
     * @author Martin Schindler
     * @return FormHelper
     */
    private function getFormHelper()
    {
        if (!$this->formHelper) {
            $phpEngine = new PhpEngine(new TemplateNameParser(realpath(self::VIEW_PATH)), new FilesystemLoader(array()));

            $formHelper = new FormHelper(new FormRenderer(new TemplatingRendererEngine($phpEngine, $this->getFormThemePaths()), null));

            $translator = new Translator($this->defaultLocale);
            $translator->addLoader('xlf', new XliffFileLoader());
            $phpEngine->addHelpers(array($formHelper, new TranslatorHelper($translator)));

            $this->formHelper = $formHelper;
        }

        return $this->formHelper;
    }

    /**
     * Wrapping the original form factory method
     * @param string $type
     * @param null $data
     * @param array $options
     * @author Martin Schindler
     * @return FormInterface
     */
    public function create($type = 'form', $data = null, array $options = array())
    {
        return $this->formFactory->create($type, $data, $options);
    }

    /**
     * Wrapping the original form factory method
     * @param int|string $name
     * @param string $type
     * @param null $data
     * @param array $options
     * @author Martin Schindler
     * @return FormInterface
     */
    public function createNamed($name, $type = 'form', $data = null, array $options = array())
    {
        return $this->formFactory->createNamed($name, $type, $data, $options);
    }

    /**
     * Wrapping the original form factory method
     * @param string $class
     * @param string $property
     * @param null $data
     * @param array $options
     * @author Martin Schindler
     * @return FormInterface
     */
    public function createForProperty($class, $property, $data = null, array $options = array())
    {
        return $this->formFactory->createForProperty($class, $property, $data, $options);
    }

    /**
     * Wrapping the original form factory method
     * @param string $type
     * @param null $data
     * @param array $options
     * @author Martin Schindler
     * @return FormBuilderInterface
     */
    public function createBuilder($type = 'form', $data = null, array $options = array())
    {
        return $this->formFactory->createBuilder($type, $data, $options);
    }

    /**
     * Wrapping the original form factory method
     * @param int|string $name
     * @param string $type
     * @param null $data
     * @param array $options
     * @author Martin Schindler
     * @return FormBuilderInterface
     */
    public function createNamedBuilder($name, $type = 'form', $data = null, array $options = array())
    {
        return $this->formFactory->createNamedBuilder($name, $type, $data, $options);
    }

    /**
     * Wrapping the original form factory method
     * @param string $class
     * @param string $property
     * @param null $data
     * @param array $options
     * @author Martin Schindler
     * @return FormBuilderInterface
     */
    public function createBuilderForProperty($class, $property, $data = null, array $options = array())
    {
        return $this->formFactory->createBuilderForProperty($class, $property, $data, $options);
    }
}