<?php

/*
 * This file is part of the Thelia package.
 * http://www.thelia.net
 *
 * (c) OpenStudio <info@thelia.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HeaderHighlights\Form;

use HeaderHighlights\HeaderHighlights;
use HeaderHighlights\Model\HeaderHighlightsImage;
use HeaderHighlights\Model\HeaderHighlightsImageQuery;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\NotBlank;
use Thelia\Core\Translation\Translator;
use Thelia\Form\BaseForm;
use Thelia\Model\CategoryQuery;

abstract class AbstractHeaderHighlightsImageForm extends BaseForm
{
    abstract protected function getDisplayType(): string;

    /**
     * {@inheritdoc}
     */
    protected function buildForm(): void
    {
        $categoriesArray = [];
        $categories = (new CategoryQuery)->find();
        $lang = ($this->request->hasSession()
            ? $this->request->getSession()->get('thelia.current.lang')
            : null) ?? \Thelia\Model\Lang::getDefaultLanguage();
        $categoriesArray[Translator::getInstance()->trans('Choose a category', [], HeaderHighlights::TRANSLATION_DOMAIN)] = "";
        foreach ($categories as $category) {
            $categoryTitle = $category
                ->getTranslation($lang->getLocale())
                ->getTitle();

            $categoriesArray[$categoryTitle] = $category->getId();
        }
        /** @var HeaderHighlightsImage $carousel */
        for ($i = 1; $i <= HeaderHighlights::IMAGE_COUNT; $i++) {
            $id = $i;

            $this->formBuilder
                ->add(
                    'image_block' . $id,
                    HiddenType::class,
                    [
                    ])
                ->add(
                    'title' . $id,
                    TextType::class,
                    [
                        'required' => false,
                        'label' => Translator::getInstance()->trans('Title', [], HeaderHighlights::TRANSLATION_DOMAIN),
                        'label_attr' => [
                            'for' => 'title_field',
                        ],
                        'attr' => [
                            'placeholder' => Translator::getInstance()->trans('Header title', [], HeaderHighlights::TRANSLATION_DOMAIN),
                            'maxlength' => 40
                        ],
                    ])
                ->add(
                    'category' . $id,
                    ChoiceType::class,
                    [
                        'choices' => $categoriesArray,
                        'label' => Translator::getInstance()->trans('Category', [], HeaderHighlights::TRANSLATION_DOMAIN),
                        'label_attr' => [
                            'for' => 'category',
                        ],
                        'required' => false,
                        'attr' => [
                            'placeholder' => Translator::getInstance()->trans(
                                'Category',
                                [],
                                HeaderHighlights::TRANSLATION_DOMAIN
                            ),
                        ],
                    ])
                ->add(
                    'call_to_action' . $id,
                    TextType::class,
                    [
                        'label' => Translator::getInstance()->trans('Call to action', [], HeaderHighlights::TRANSLATION_DOMAIN),
                        'label_attr' => [
                            'for' => 'call_to_action',
                        ],
                        'required' => false,
                        'attr' => [
                            'placeholder' => Translator::getInstance()->trans('Button Libelle', [], HeaderHighlights::TRANSLATION_DOMAIN),
                            'maxlength' => 50
                        ]
                    ])
                ->add(
                    'url' . $id,
                    TextType::class,
                    [
                        'label' => Translator::getInstance()->trans('url', [], HeaderHighlights::TRANSLATION_DOMAIN),
                        'label_attr' => [
                            'for' => 'url',
                        ],
                        'required' => false,
                        'attr' => [
                            'placeholder' => Translator::getInstance()->trans('Button Link', [], HeaderHighlights::TRANSLATION_DOMAIN),
                        ]
                    ])
                ->add(
                    'catchphrase' . $id,
                    TextareaType::class,
                    [
                        'required' => false,
                        'label' => Translator::getInstance()->trans('Catchphrase', [], HeaderHighlights::TRANSLATION_DOMAIN),
                        'label_attr' => [
                            'for' => 'catchphrase',
                        ],
                        'attr' => [
                            'rows' => 5,
                            'placeholder' => Translator::getInstance()->trans('Your catchphrase', [], HeaderHighlights::TRANSLATION_DOMAIN),
                            'maxlength' => 100
                        ],
                    ])
                ->add(
                    'image' . $id,
                    FileType::class,
                    [
                        'constraints' => [
                            new Image(mimeTypes: ['image/gif', 'image/jpeg', 'image/png', 'image/webp']),
                        ],
                        'required' => false,
                        'label' => Translator::getInstance()->trans('Image', [], HeaderHighlights::TRANSLATION_DOMAIN),
                        'label_attr' => [
                            'for' => 'file',
                            'help' => Translator::getInstance()->trans('Recommended size for the picture is 780 x 480',
                                [],
                                HeaderHighlights::TRANSLATION_DOMAIN
                            )
                        ],
                    ]
                )
                ->add(
                    'display_type' . $id,
                    HiddenType::class, []
                );
        }
    }
}
