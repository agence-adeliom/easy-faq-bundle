<?php

declare(strict_types=1);

namespace Adeliom\EasyFaqBundle\Tests\Controller;

use Adeliom\EasyFieldsBundle\Admin\Field\AssociationField;
use Adeliom\EasyFaqBundle\Controller\EntryCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Provider\AdminContextProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

#[CoversClass(\Adeliom\EasyFaqBundle\Controller\EntryCrudController::class)]
final class EntryCrudControllerTest extends TestCase
{
    public function testCrudConfigurationSetsThemesTitlesAndLabels(): void
    {
        $controller = new EntryCrudControllerDouble();
        $dto = $controller->configureCrud(Crud::new())->getAsDto();

        self::assertContains('@EasyFields/form/association_widget.html.twig', $dto->getFormThemes());
        self::assertContains('@EasyMedia/form/easy-media.html.twig', $dto->getFormThemes());
        self::assertSame('easy.faq.admin.crud.label.entry.singular', (string) $dto->getEntityLabelInSingular());
        self::assertSame('easy.faq.admin.crud.title.entry.index', $dto->getCustomPageTitle(Crud::PAGE_INDEX)?->getMessage());
    }

    public function testFilterConfigurationAddsStatusChoiceFilter(): void
    {
        $controller = new EntryCrudControllerDouble();
        $filters = $controller->configureFilters(Filters::new())->getAsDto();

        self::assertNotNull($filters->getFilter('state'));
    }

    public function testActionsConfigurationRenamesBuiltInActionsAndAddsDetail(): void
    {
        $controller = new EntryCrudControllerDouble();
        $actions = Actions::new();
        foreach ([Crud::PAGE_INDEX, Crud::PAGE_EDIT, Crud::PAGE_NEW, Crud::PAGE_DETAIL] as $page) {
            foreach ([Action::INDEX, Action::EDIT, Action::DELETE, Action::NEW] as $action) {
                $actions->add($page, $action);
            }
        }

        $configured = $controller->configureActions($actions)->getAsDto(null);

        self::assertSame('action.detail', $configured->getAction(Crud::PAGE_INDEX, Action::DETAIL)?->getLabel()?->getMessage());
        self::assertSame('easy.faq.admin.crud.label.entry.edit', $configured->getAction(Crud::PAGE_EDIT, Action::EDIT)?->getLabel());
    }

    public function testFieldGroupsExposeExpectedFieldDefinitions(): void
    {
        $controller = new EntryCrudControllerDouble();
        $controller->setParameters([
            'easy_faq.category.crud' => 'App\\Controller\\Admin\\CategoryCrudController',
        ]);
        $controller->setContainer($this->createContainer());

        $fields = iterator_to_array($controller->configureFields(Crud::PAGE_EDIT), false);

        self::assertCount(14, $fields);
        self::assertSame('name', $fields[2]->getAsDto()->getProperty());
        self::assertSame('answer', $fields[3]->getAsDto()->getProperty());
        self::assertSame('category', $fields[9]->getAsDto()->getProperty());
        self::assertInstanceOf(AssociationField::class, $fields[9]);
        self::assertSame('state', $fields[11]->getAsDto()->getProperty());
        self::assertInstanceOf(ChoiceField::class, $fields[11]);
        self::assertSame('publishDate', $fields[12]->getAsDto()->getProperty());
    }

    private function createContainer(): ContainerInterface
    {
        $provider = new AdminContextProvider(new \Symfony\Component\HttpFoundation\RequestStack());

        return new class($provider) implements ContainerInterface {
            public function __construct(private AdminContextProvider $provider)
            {
            }

            public function get(string $id)
            {
                if (AdminContextProvider::class === $id) {
                    return $this->provider;
                }

                throw new \InvalidArgumentException('Unknown service '.$id);
            }

            public function has(string $id): bool
            {
                return AdminContextProvider::class === $id;
            }
        };
    }
}

final class EntryCrudControllerDouble extends EntryCrudController
{
    private array $parameters = [];

    public static function getEntityFqcn(): string
    {
        return \stdClass::class;
    }

    public function setParameters(array $parameters): void
    {
        $this->parameters = $parameters;
    }

    protected function getParameter(string $name): array|bool|string|int|float|\UnitEnum|null
    {
        return $this->parameters[$name];
    }
}
