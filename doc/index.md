# Setup database

## Using doctrine migrations

```bash
php bin/console doctrine:migration:diff
php bin/console doctrine:migration:migrate
```

## Without

```bash
php bin/console doctrine:schema:update --force
```

# Manage pages in your Easyadmin dashboard

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
