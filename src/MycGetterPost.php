<?php

namespace RadishLab\MycGetter;

class MycGetterPost extends MycGetterBase
{
    /**
     * Escape default post fields
     *
     * @param object $post The WP_Post object
     * @param array $args The settings to use
     * @return array
     */
    public function __construct($post, $args)
    {
        $this->args = $args;
        $this->post = $post;
        $this->postID = $post->ID;
        $this->postType = $post->post_type;
    }

    public function escapePostFields()
    {
        $data = [];
        foreach ($this->args['fields'] ?? [] as $field) {
            switch ($field) {
                case 'title':
                    $data[$field] = $this->getTitle();
                    break;
                case 'url':
                    $data[$field] = $this->getURL();
                    break;
                case 'image':
                    $data[$field] = $this->getImage();
                    break;
                case 'text':
                    $data[$field] = $this->getText();
                    break;
                case 'excerpt':
                    $data[$field] = $this->getExcerpt();
                    break;
                case 'date':
                    $data[$field] = $this->getPostDate();
                    break;
                default:
                    $data[$field] = false;
                    break;
            }
        }

        return $data;
    }

    protected function getId()
    {
        $id = absint($this->post->ID);
        return apply_filters('myc_getter_get_id', $id, $this->postType, $this->postID);
    }

    protected function getSlug()
    {
        $slug = esc_attr($this->post->post_name);
        return apply_filters('myc_getter_get_slug', $slug, $this->postType, $this->postID);
    }

    protected function getTitle()
    {
        $title = esc_html($this->post->post_title);
        return apply_filters('myc_getter_get_title', $title, $this->postType, $this->postID);
    }

    protected function getURL()
    {
        $url = esc_url(get_permalink($this->postID));
        return apply_filters('myc_getter_get_url', $url, $this->postType, $this->postID);
    }

    protected function getImage()
    {
        $image = $this->args['image_type'] == 'html'
            ? get_the_post_thumbnail($this->postID, $this->args['image_size'])
            : esc_url(get_the_post_thumbnail_url($this->postID, $this->args['image_size']));

        return apply_filters('myc_getter_get_image', $image, $this->postType, $this->postID);
    }

    protected function getText()
    {
        $escapedText = wp_kses_post($this->post->post_content);
        $text = apply_filters('the_content', $escapedText);
        return apply_filters('myc_getter_get_text', $text, $this->postType, $this->postID);
    }

    protected function getExcerpt()
    {
        $excerpt = wp_kses_post(get_the_excerpt($this->post));
        return apply_filters('myc_getter_get_excerpt', $excerpt, $this->postType, $this->postID);
    }

    protected function getPostDate()
    {
        $data = [
            'date'      => esc_attr(get_the_date('', $this->postID)),
            'global'    => esc_attr(get_post_time('c', true, $this->postID)),
        ];

        return apply_filters('myc_getter_get_date', $data, $this->postType, $this->postID);
    }
}
