<?php

namespace Adeliom\EasyFaqBundle\Controller;


use Adeliom\EasyCommonBundle\Enum\ThreeStateStatusEnum;
use Adeliom\EasyFieldsBundle\Admin\Field\AssociationField;
use Adeliom\EasyFieldsBundle\Admin\Field\EnumField;
use Adeliom\EasySeoBundle\Admin\Field\SEOField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Provider\AdminContextProvider;

abstract class EntryCrudController extends AbstractCrudController
{

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->addFormTheme('@EasyFields/form/association_widget.html.twig')
            ->addFormTheme('@EasyCommon/crud/custom_panel.html.twig')
            ->addFormTheme('@EasyMedia/form/easy-media.html.twig')

            ->setPageTitle(Crud::PAGE_INDEX, "easy.faq.admin.crud.title.entry." . Crud::PAGE_INDEX)
            ->setPageTitle(Crud::PAGE_EDIT, "easy.faq.admin.crud.title.entry." . Crud::PAGE_EDIT)
            ->setPageTitle(Crud::PAGE_NEW, "easy.faq.admin.crud.title.entry." . Crud::PAGE_NEW)
            ->setPageTitle(Crud::PAGE_DETAIL, "easy.faq.admin.crud.title.entry." . Crud::PAGE_DETAIL)
            ->setEntityLabelInSingular("easy.faq.admin.crud.label.entry.singular")
            ->setEntityLabelInPlural("easy.faq.admin.crud.label.entry.plural")
            ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        $filters->add(ChoiceFilter::new("state","Status")->setChoices(ThreeStateStatusEnum::toArray()));
        return $filters;
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions = parent::configureActions($actions);
        $pages = [Crud::PAGE_INDEX, Crud::PAGE_EDIT, Crud::PAGE_NEW, Crud::PAGE_DETAIL];
        foreach ($pages as $page) {
            $pageActions = $actions->getAsDto($page)->getActions();
            foreach ($pageActions as $action) {
                $action->setLabel("easy.faq.admin.crud.label.entry." . $action->getName());
                $actions->remove($page, $action->getAsConfigObject());
                $actions->add($page, $action->getAsConfigObject());
            }
        }
        $actions->add(Crud::PAGE_INDEX, Action::DETAIL);
        return $actions;
    }

    public function configureFields(string $pageName): iterable
    {
        $context = $this->get(AdminContextProvider::class)->getContext();
        $subject = $context->getEntity();

        yield IdField::new('id')->hideOnForm();
        yield from $this->informationsFields($pageName, $subject);
        yield from $this->seoFields($pageName, $subject);
        yield from $this->publishFields($pageName, $subject);
        yield from $this->metadataFields($pageName, $subject);
    }

    public function informationsFields(string $pageName, $subject): iterable
    {
        yield FormField::addPanel("easy.faq.admin.panel.information")->addCssClass("col-12");
        yield TextField::new('name', "easy.faq.admin.field.question")
            ->setRequired(true)
            ->setColumns(12);

        yield TextareaField::new('answer', "easy.faq.admin.field.answer")
            ->setRequired(true)
            ->setColumns(12);
    }

    public function metadataFields(string $pageName, $subject): iterable
    {
        yield FormField::addPanel("easy.faq.admin.panel.metadatas")->collapsible()->addCssClass("col-4");
        yield SlugField::new('slug', "easy.faq.admin.field.slug")
            ->setRequired(true)
            ->hideOnIndex()
            ->setTargetFieldName('name')
            ->setUnlockConfirmationMessage("easy.faq.admin.field.slug_edit")
            ->setColumns(12);
        yield AssociationField::new("category", "easy.faq.admin.field.category")
            ->autocomplete()
            ->listSelector(true)
            ->setCrudController($this->getParameter("easy_faq.category.crud"))
        ;
    }

    public function seoFields(string $pageName, $subject): iterable
    {
        yield FormField::addPanel("easy.faq.admin.panel.seo")->collapsible()->addCssClass("col-4");
        yield SEOField::new("seo");
    }

    public function publishFields(string $pageName, $subject): iterable
    {
        yield FormField::addPanel("easy.faq.admin.panel.publication")->collapsible()->addCssClass("col-4");
        yield EnumField::new("state", 'easy.faq.admin.field.state')
            ->setEnum(ThreeStateStatusEnum::class)
            ->setRequired(true)
            ->renderExpanded(true)
            ->renderAsBadges(true);
        yield DateTimeField::new('publishDate', "easy.faq.admin.field.publishDate")->setFormat('Y-MM-dd HH:mm')
            ->setRequired(true)
            ->hideOnIndex()
            ->setColumns(6);
        yield DateTimeField::new('unpublishDate', "easy.faq.admin.field.unpublishDate")->setFormat('Y-MM-dd HH:mm')
            ->setRequired(false)
            ->hideOnIndex()
            ->setColumns(6);
    }
}
