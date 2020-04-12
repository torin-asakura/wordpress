<?php

namespace YOOtheme\Builder\Wordpress\Source\Type;

use function YOOtheme\app;
use YOOtheme\Arr;
use YOOtheme\Builder\Source;
use YOOtheme\Path;
use YOOtheme\Str;
use YOOtheme\View;

class PostType
{
    /**
     * @var \WP_Post_Type
     */
    protected $type;

    /**
     * @var array
     */
    protected $features = [
        'title' => 'title',
        'author' => 'author',
        'editor' => 'content',
        'excerpt' => 'excerpt',
        'thumbnail' => 'featuredImage',
        'comments' => 'commentCount',
    ];

    /**
     * Constructor.
     *
     * @param \WP_Post_Type $type
     */
    public function __construct(\WP_Post_Type $type)
    {
        $this->type = $type;
    }

    /**
     * @param Source $source
     *
     * @return array
     */
    public function __invoke(Source $source)
    {
        $taxonomies = array_column(array_filter(get_object_taxonomies($this->type->name, 'object'), function ($taxonomy) {
            return $taxonomy->public && $taxonomy->show_ui && $taxonomy->show_in_nav_menus;
        }), 'name', 'label');

        $fields = [

            'title' => [
                'type' => 'String',
                'metadata' => [
                    'label' => 'Title',
                    'filters' => ['limit'],
                ],
            ],

            'content' => [
                'type' => 'String',
                'metadata' => [
                    'label' => 'Content',
                    'filters' => ['limit'],
                ],
            ],

            'teaser' => [
                'type' => 'String',
                'metadata' => [
                    'label' => 'Teaser',
                    'filters' => ['limit'],
                ],
            ],

            'excerpt' => [
                'type' => 'String',
                'metadata' => [
                    'label' => 'Excerpt',
                    'filters' => ['limit'],
                ],
            ],

            'date' => [
                'type' => 'String',
                'metadata' => [
                    'label' => 'Date',
                    'filters' => ['date'],
                ],
            ],

            'modified' => [
                'type' => 'String',
                'metadata' => [
                    'label' => 'Modified',
                    'filters' => ['date'],
                ],
            ],

            'commentCount' => [
                'type' => 'String',
                'metadata' => [
                    'label' => 'Comment Count',
                ],
            ],

            'metaString' => [
                'type' => 'String',
                'args' => [
                    'format' => [
                        'type' => 'String',
                    ],
                    'separator' => [
                        'type' => 'String',
                    ],
                    'link_style' => [
                        'type' => 'String',
                    ],
                    'show_publish_date' => [
                        'type' => 'Boolean',
                    ],
                    'show_author' => [
                        'type' => 'Boolean',
                    ],
                    'show_comments' => [
                        'type' => 'Boolean',
                    ],
                    'show_taxonomy' => [
                        'type' => 'String',
                    ],
                ],
                'metadata' => [
                    'label' => 'Meta',
                    'arguments' => [

                        'format' => [
                            'label' => 'Format',
                            'description' => 'Display the meta text in a sentence or a horizontal list.',
                            'type' => 'select',
                            'default' => 'list',
                            'options' => [
                                'List' => 'list',
                                'Sentence' => 'sentence',
                            ],
                        ],
                        'separator' => [
                            'label' => 'Separator',
                            'description' => 'Set the separator between fields.',
                            'default' => '|',
                            'enable' => 'arguments.format === "list"',
                        ],
                        'link_style' => [
                            'label' => 'Link Style',
                            'description' => 'Set the link style.',
                            'type' => 'select',
                            'default' => '',
                            'options' => [
                                'Default' => '',
                                'Muted' => 'link-muted',
                                'Text' => 'link-text',
                                'Heading' => 'link-heading',
                                'Reset' => 'link-reset',
                            ],
                        ],
                        'show_publish_date' => [
                            'label' => 'Display',
                            'description' => 'Show or hide fields in the meta text.',
                            'type' => 'checkbox',
                            'default' => true,
                            'text' => 'Show date',
                        ],
                        'show_author' => [
                            'type' => 'checkbox',
                            'default' => true,
                            'text' => 'Show author',
                        ],
                        'show_comments' => [
                            'type' => 'checkbox',
                            'default' => true,
                            'text' => 'Show comment count',
                        ],
                        'show_taxonomy' => [
                            'type' => 'select',
                            'default' => $this->type->name === 'post' ? 'category' : '',
                            'show' => (bool) $taxonomies,
                            'options' => [
                                'Hide term list' => '',
                            ] + array_combine(array_map(function ($name) { return "Show {$name}"; }, array_keys($taxonomies)), $taxonomies),
                        ],

                    ],
                ],
            ],

            'featuredImage' => [
                'type' => 'String',
                'metadata' => [
                    'label' => 'Featured Image',
                ],
            ],

            'link' => [
                'type' => 'String',
                'metadata' => [
                    'label' => 'Link',
                ],
            ],

            'author' => [
                'type' => 'User',
                'metadata' => [
                    'label' => 'Author',
                ],
            ],

            'field' => [
                'type' => Str::camelCase([$this->type->name, 'Fields'], true),
                'metadata' => [
                    'label' => 'Fields',
                ],
            ],

        ];

        $metadata = [
            'type' => true,
            'label' => $this->type->labels->singular_name,
        ];

        $resolvers = $source->mapResolvers($this);

        foreach ($taxonomies as $label => $name) {
            $fields["{$name}String"] = [
                'type' => 'String',
                'args' => [
                    'separator' => [
                        'type' => 'String',
                    ],
                    'link_style' => [
                        'type' => 'String',
                    ],
                ],
                'metadata' => [
                    'label' => $label,
                    'arguments' => [
                        'separator' => [
                            'label' => 'Separator',
                            'description' => 'Set the separator between terms.',
                            'default' => ', ',
                        ],
                        'link_style' => [
                            'label' => 'Link Style',
                            'description' => 'Set the link style.',
                            'type' => 'select',
                            'default' => '',
                            'options' => [
                                'Default' => '',
                                'Muted' => 'link-muted',
                                'Text' => 'link-text',
                                'Heading' => 'link-heading',
                                'Reset' => 'link-reset',
                            ],
                        ],
                    ],
                ],
            ];
            $resolvers["{$name}String"] = function ($item, $args) use ($name) {
                $args += ['separator' => ', ', 'link_style' => ''];
                $before = $args['link_style'] ? "<span class=\"uk-{$args['link_style']}\">" : '';
                $after = $args['link_style'] ? '</span>' : '';
                return get_the_term_list($item->ID, $name, $before, $args['separator'], $after) ?: null;
            };
        }

        // omit unsupported features
        if ($values = array_diff_key($this->features, get_all_post_type_supports($this->type->name))) {
            $fields = Arr::omit($fields, $values);
        }

        return compact('fields', 'resolvers', 'metadata');
    }

    public function title($post)
    {
        return $post->post_title;
    }

    public function content($post)
    {
        $content = get_the_content(null, false, $post);
        $content = apply_filters('the_content', $content);
        $content = str_replace(']]>', ']]&gt;', $content);

        return $content;
    }

    public function teaser($post)
    {
        if ($excerpt = get_the_excerpt($post)) {
            return $excerpt;
        }

        $extended = get_extended($post->post_content);

        return $extended['main'];
    }

    public function date($post)
    {
        return $post->post_date;
    }

    public function modified($post)
    {
        return $post->post_modified;
    }

    public function commentCount($post)
    {
        return $post->comment_count;
    }

    public function link($post)
    {
        return get_permalink($post);
    }

    public function featuredImage($post)
    {
        return ($image = get_post_thumbnail_id($post)) ? wp_get_attachment_url($image) : '';
    }

    public function author($post)
    {
        return get_userdata($post->post_author);
    }

    public function metaString($post, array $args)
    {
        $args += ['format' => 'list', 'separator' => '|', 'link_style' => '', 'show_publish_date' => true, 'show_author' => true, 'show_comments' => true, 'show_taxonomy' => $this->type->name === 'post' ? 'category' : ''];

        return app(View::class)->render(Path::get('../../templates/meta'), compact('post', 'args'));
    }

    public function field($post)
    {
        return $post;
    }
}
