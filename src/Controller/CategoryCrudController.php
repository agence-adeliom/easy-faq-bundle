<?php

namespace Adeliom\EasyFaqBundle\Controller;

use Adeliom\EasyCommonBundle\Enum\ThreeStateStatusEnum;
use Adeliom\EasySeoBundle\Admin\Field\SEOField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Provider\AdminContextProvider;

abstract class CategoryCrudController extends AbstractCrudController
{
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->addFormTheme('@EasyMedia/form/easy-media.html.twig')

            ->setPageTitle(Crud::PAGE_INDEX, 'easy.faq.admin.crud.title.category.'.Crud::PAGE_INDEX)
            ->setPageTitle(Crud::PAGE_EDIT, 'easy.faq.admin.crud.title.category.'.Crud::PAGE_EDIT)
            ->setPageTitle(Crud::PAGE_NEW, 'easy.faq.admin.crud.title.category.'.Crud::PAGE_NEW)
            ->setPageTitle(Crud::PAGE_DETAIL, 'easy.faq.admin.crud.title.category.'.Crud::PAGE_DETAIL)
            ->setEntityLabelInSingular('easy.faq.admin.crud.label.category.singular')
            ->setEntityLabelInPlural('easy.faq.admin.crud.label.category.plural')
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        $filters->add(ChoiceFilter::new('state', 'Status')->setChoices(ThreeStateStatusEnum::toArray()));

        return $filters;
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions = parent::configureActions($actions);
        $pages = [Crud::PAGE_INDEX, Crud::PAGE_EDIT, Crud::PAGE_NEW, Crud::PAGE_DETAIL];
        foreach ($pages as $page) {
            $pageActions = $actions->getAsDto($page)->getActions();
            foreach ($pageActions as $action) {
                $action->setLabel('easy.faq.admin.crud.label.category.'.$action->getName());
                $actions->remove($page, $action->getAsConfigObject());
                $actions->add($page, $action->getAsConfigObject());
            }
        }

        $actions->add(Crud::PAGE_INDEX, Action::DETAIL);

        return $actions;
    }

    public function configureFields(string $pageName): iterable
    {
        $context = $this->container->get(AdminContextProvider::class)->getContext();
        $subject = $context?->getEntity();

        yield FormField::addTab('easy.faq.admin.panel.information');
        yield IdField::new('id')->hideOnForm();
        yield from $this->informationsFields($pageName, $subject);
        yield FormField::addTab('easy.faq.admin.panel.publication');
        yield from $this->seoFields($pageName, $subject);
        yield from $this->metadataFields($pageName, $subject);
        yield from $this->publishFields($pageName, $subject);
    }

    /**
     * @return FieldInterface[]
     */
    public function informationsFields(string $pageName, ?EntityDto $subject): iterable
    {
        yield FormField::addPanel('easy.faq.admin.panel.information')->addCssClass('col-8');
        yield TextField::new('name', 'easy.faq.admin.field.name')
            ->setRequired(true)
            ->setColumns(12);
    }

    /**
     * @return FieldInterface[]
     */
    public function metadataFields(string $pageName, ?EntityDto $subject): iterable
    {
        yield FormField::addPanel('easy.faq.admin.panel.metadatas')->addCssClass('col-4');
        yield SlugField::new('slug', 'easy.faq.admin.field.slug')
            ->setRequired(true)
            ->hideOnIndex()
            ->setTargetFieldName('name')
            ->setUnlockConfirmationMessage('easy.faq.admin.field.slug_edit')
            ->setColumns(12);
    }

    /**
     * @return FieldInterface[]
     */
    public function seoFields(string $pageName, ?EntityDto $subject): iterable
    {
        yield FormField::addPanel('easy.faq.admin.panel.seo')->addCssClass('col-4');
        yield SEOField::new('seo');
    }

    /**
     * @return FieldInterface[]
     */
    public function publishFields(string $pageName, ?EntityDto $subject): iterable
    {
        yield FormField::addPanel('easy.faq.admin.panel.publication')->addCssClass('col-4');
        yield BooleanField::new('status', 'easy.faq.admin.field.state')
            ->setRequired(true)
            ->renderAsSwitch(true);
    }
}
