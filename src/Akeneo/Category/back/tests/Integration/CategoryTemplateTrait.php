<?php

namespace Akeneo\Category\back\tests\Integration;

use Akeneo\Category\Domain\Model\Attribute\AttributeImage;
use Akeneo\Category\Domain\Model\Attribute\AttributeRichText;
use Akeneo\Category\Domain\Model\Attribute\AttributeText;
use Akeneo\Category\Domain\Model\Attribute\AttributeTextArea;
use Akeneo\Category\Domain\Model\Template;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeAdditionalProperties;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCode;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCollection;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsLocalizable;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsRequired;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsScopable;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeOrder;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeUuid;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\Template\TemplateCode;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use Ramsey\Uuid\Uuid;

trait CategoryTemplateTrait
{
    public function generateStaticCategoryTemplate(
        ?string $templateUuid = null,
        ?int $categoryTreeId = 1
    ): Template
    {
        if ($templateUuid === null) {
            $templateUuid = TemplateUuid::fromString(Uuid::uuid4());
        } else {
            $templateUuid = TemplateUuid::fromString($templateUuid);
        }

        return new Template(
            $templateUuid,
            new TemplateCode('default_template'),
            LabelCollection::fromArray(['en_US' => 'Default template']),
            new CategoryId($categoryTreeId),
            AttributeCollection::fromArray([
                AttributeRichText::create(
                    AttributeUuid::fromString('840fcd1a-f66b-4f0c-9bbd-596629732950'),
                    new AttributeCode('description'),
                    AttributeOrder::fromInteger(1),
                    AttributeIsRequired::fromBoolean(true),
                    AttributeIsScopable::fromBoolean(true),
                    AttributeIsLocalizable::fromBoolean(true),
                    LabelCollection::fromArray(['en_US' => 'Description']),
                    $templateUuid,
                    AttributeAdditionalProperties::fromArray([])
                ),
                AttributeImage::create(
                    AttributeUuid::fromString('8dda490c-0fd1-4485-bdc5-342929783d9a'),
                    new AttributeCode('banner_image'),
                    AttributeOrder::fromInteger(2),
                    AttributeIsRequired::fromBoolean(true),
                    AttributeIsScopable::fromBoolean(false),
                    AttributeIsLocalizable::fromBoolean(false),
                    LabelCollection::fromArray(['en_US' => 'Banner image']),
                    $templateUuid,
                    AttributeAdditionalProperties::fromArray([])
                ),
                AttributeText::create(
                    AttributeUuid::fromString('4873080d-32a3-42a7-ae5c-1be518e40f3d'),
                    new AttributeCode('seo_meta_title'),
                    AttributeOrder::fromInteger(3),
                    AttributeIsRequired::fromBoolean(true),
                    AttributeIsScopable::fromBoolean(true),
                    AttributeIsLocalizable::fromBoolean(true),
                    LabelCollection::fromArray(['en_US' => 'SEO Meta Title']),
                    $templateUuid,
                    AttributeAdditionalProperties::fromArray([])
                ),
                AttributeTextArea::create(
                    AttributeUuid::fromString('69e251b3-b876-48b5-9c09-92f54bfb528d'),
                    new AttributeCode('seo_meta_description'),
                    AttributeOrder::fromInteger(4),
                    AttributeIsRequired::fromBoolean(true),
                    AttributeIsScopable::fromBoolean(true),
                    AttributeIsLocalizable::fromBoolean(true),
                    LabelCollection::fromArray(['en_US' => 'SEO Meta Description']),
                    $templateUuid,
                    AttributeAdditionalProperties::fromArray([])
                ),
                AttributeTextArea::create(
                    AttributeUuid::fromString('4ba33f06-de92-4366-8322-991d1bad07b9'),
                    new AttributeCode('seo_keywords'),
                    AttributeOrder::fromInteger(5),
                    AttributeIsRequired::fromBoolean(true),
                    AttributeIsScopable::fromBoolean(true),
                    AttributeIsLocalizable::fromBoolean(true),
                    LabelCollection::fromArray(['en_US' => 'SEO Keywords']),
                    $templateUuid,
                    AttributeAdditionalProperties::fromArray([])
                ),
            ])
        );
    }
}
