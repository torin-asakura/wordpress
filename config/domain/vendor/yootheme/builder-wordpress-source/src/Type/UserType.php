<?php

namespace YOOtheme\Builder\Wordpress\Source\Type;

use YOOtheme\Builder\Source;

class UserType
{
    /**
     * @param Source $source
     *
     * @return array
     */
    public function __invoke(Source $source)
    {
        return [

            'fields' => [

                'name' => [
                    'type' => 'String',
                    'metadata' => [
                        'label' => 'Name',
                        'filters' => ['limit'],
                    ],
                ],

                'nicename' => [
                    'type' => 'String',
                    'metadata' => [
                        'label' => 'Nicename',
                        'filters' => ['limit'],
                    ],
                ],

                'nickname' => [
                    'type' => 'String',
                    'metadata' => [
                        'label' => 'Nickname',
                        'filters' => ['limit'],
                    ],
                ],

                'firstName' => [
                    'type' => 'String',
                    'metadata' => [
                        'label' => 'First name',
                        'filters' => ['limit'],
                    ],
                ],

                'lastName' => [
                    'type' => 'String',
                    'metadata' => [
                        'label' => 'Last name',
                        'filters' => ['limit'],
                    ],
                ],

                'description' => [
                    'type' => 'String',
                    'metadata' => [
                        'label' => 'Description',
                        'filters' => ['limit'],
                    ],
                ],

                'email' => [
                    'type' => 'String',
                    'metadata' => [
                        'label' => 'Email',
                        'filters' => ['limit'],
                    ],
                ],

                'registered' => [
                    'type' => 'String',
                    'metadata' => [
                        'label' => 'Registered',
                        'filters' => ['date'],
                    ],
                ],

                'url' => [
                    'type' => 'String',
                    'metadata' => [
                        'label' => 'Website Url',
                        'filters' => ['limit'],
                    ],
                ],

                'link' => [
                    'type' => 'String',
                    'metadata' => [
                        'label' => 'Link',
                    ],
                ],

                'field' => [
                    'type' => 'UserFields',
                    'metadata' => [
                        'label' => 'Fields',
                    ],
                ],

            ],

            'metadata' => [
                'type' => true,
                'label' => 'User',
            ],

            'resolvers' => $source->mapResolvers($this),

        ];
    }

    public function name($user)
    {
        return $user->display_name;
    }

    public function nicename($user)
    {
        return $user->user_nicename;
    }

    public function nickname($user)
    {
        return $user->nickname;
    }

    public function firstName($user)
    {
        return $user->first_name;
    }

    public function lastName($user)
    {
        return $user->last_name;
    }

    public function email($user)
    {
        return $user->user_email;
    }

    public function registered($user)
    {
        return $user->user_registered;
    }

    public function url($user)
    {
        return $user->user_url;
    }

    public function link($user)
    {
        return get_author_posts_url($user->ID);
    }

    public function field($user)
    {
        return $user;
    }
}
