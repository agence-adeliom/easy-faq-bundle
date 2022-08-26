
![Adeliom](https://adeliom.com/public/uploads/2017/09/Adeliom_logo.png)
[![Quality gate](https://sonarcloud.io/api/project_badges/quality_gate?project=agence-adeliom_easy-faq-bundle)](https://sonarcloud.io/dashboard?id=agence-adeliom_easy-faq-bundle)

# Easy FAQ Bundle

Provide a basic FAQ system for Easyadmin.


## Features

- A Easyadmin CRUD interface to manage FAQ elements

## Installation with Symfony Flex

Add our recipes endpoint

```json
{
  "extra": {
    "symfony": {
      "endpoint": [
        "https://api.github.com/repos/agence-adeliom/symfony-recipes/contents/index.json?ref=flex/main",
        ...
        "flex://defaults"
      ],
      "allow-contrib": true
    }
  }
}
```

Install with composer

```bash
composer require agence-adeliom/easy-faq-bundle
```

### Setup database

#### Using doctrine migrations

```bash
php bin/console doctrine:migration:diff
php bin/console doctrine:migration:migrate
```

#### Without

```bash
php bin/console doctrine:schema:update --force
```


## Documentation

### Manage in your Easyadmin dashboard

Go to your dashboard controller, example : `src/Controller/Admin/DashboardController.php`

```php
<?php

namespace App\Controller\Admin;

...
use App\Entity\EasyFaq\Entry;
use App\Entity\EasyFaq\Category;

class DashboardController extends AbstractDashboardController
{
    ...
    public function configureMenuItems(): iterable
    {
        ...
        yield MenuItem::section('easy.faq.faq'); // (Optional)
        yield MenuItem::linkToCrud('easy.faq.admin.menu.entries', 'fa fa-file-alt', Entry::class);
        yield MenuItem::linkToCrud('easy.faq.admin.menu.categories', 'fa fa-folder', Category::class);

        ...
```

### Customize faq's root path

```yaml
#config/packages/easy_faq.yaml
easy_faq:
  ...
  page:
    root_path: '/blog'
```
NOTE : You will need to clear your cache after change because the RouteLoader need to be cleared.


## License

[MIT](https://choosealicense.com/licenses/mit/)


## Authors

- [@arnaud-ritti](https://github.com/arnaud-ritti)
- [@JeromeEngelnAdeliom](https://github.com/JeromeEngelnAdeliom)

  
