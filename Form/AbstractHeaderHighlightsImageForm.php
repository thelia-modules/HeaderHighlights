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
        $lang = $this->request->getSession()->get('thelia.current.lang');

        foreach ($categories as $category) {
            $categoryTitle = $category
                ->getTranslation($lang->getLocale())
                ->getTitle();

            $categoriesArray[$categoryTitle] = $category->getId();
        }

        $images = HeaderHighlightsImageQuery::create()
            ->filterByDisplayType($this->getDisplayType())
            ->orderByPosition()
            ->find();

        /** @var HeaderHighlightsImage $carousel */
        foreach ($images as $image) {
            $id = $image->getId();

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
                        'required' => true,
                        'label' => Translator::getInstance()->trans('Title', [], HeaderHighlights::DOMAIN_NAME),
                        'label_attr' => [
                            'for' => 'title_field',
                        ],
                        'attr' => [
                            'placeholder' => Translator::getInstance()->trans('Header title', [], HeaderHighlights::DOMAIN_NAME),
                            'maxlength' => 40
                        ],
                    ])
                ->add(
                    'category' . $id,
                    ChoiceType::class,
                    [
                        'choices' => $categoriesArray,
                        'label' => Translator::getInstance()->trans('Category', [], HeaderHighlights::DOMAIN_NAME),
                        'label_attr' => [
                            'for' => 'category',
                        ],
                        'required' => true,
                        'attr' => [
                            'placeholder' => Translator::getInstance()->trans(
                                'Category',
                                [],
                                HeaderHighlights::DOMAIN_NAME
                            ),
                        ],
                    ])
                ->add(
                    'call_to_action' . $id,
                    TextType::class,
                    [
                        'label' => Translator::getInstance()->trans('Call to action', [], HeaderHighlights::DOMAIN_NAME),
                        'label_attr' => [
                            'for' => 'call_to_action',
                        ],
                        'attr' => [
                            'placeholder' => Translator::getInstance()->trans('Button Libelle', [], HeaderHighlights::DOMAIN_NAME),
                            'maxlength' => 50
                        ]
                    ])
                ->add(
                    'url' . $id,
                    TextType::class,
                    [
                        'label' => Translator::getInstance()->trans('url', [], HeaderHighlights::DOMAIN_NAME),
                        'label_attr' => [
                            'for' => 'url',
                        ],
                        'attr' => [
                            'placeholder' => Translator::getInstance()->trans('Button Link', [], HeaderHighlights::DOMAIN_NAME),
                        ]
                    ])
                ->add(
                    'catchphrase' . $id,
                    TextareaType::class,
                    [
                        'required' => false,
                        'label' => Translator::getInstance()->trans('Catchphrase', [], HeaderHighlights::DOMAIN_NAME),
                        'label_attr' => [
                            'for' => 'catchphrase',
                        ],
                        'attr' => [
                            'rows' => 5,
                            'placeholder' => Translator::getInstance()->trans('Your catchphrase', [], HeaderHighlights::DOMAIN_NAME),
                            'maxlength' => 100
                        ],
                    ])
                ->add(
                    'image' . $id,
                    FileType::class,
                    [
                        'constraints' => [
                            new Image(),
                        ],
                        'required' => false,
                        'label' => Translator::getInstance()->trans('Image', [], HeaderHighlights::DOMAIN_NAME),
                        'label_attr' => [
                            'for' => 'file',
                            'help' => Translator::getInstance()->trans('Recommended size for the picture is 780 x 480',
                                [],
                                HeaderHighlights::DOMAIN_NAME
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
