<?php

namespace Akeneo\Category\Application\Template;

use Akeneo\Category\Domain\Model\Template;

interface TemplateRepository
{
    public function insert(Template $templateModel);

    public function update(Template $templateModel);
}
