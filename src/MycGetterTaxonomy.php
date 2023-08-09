<?php

namespace RadishLab\MycGetter;

class MycGetterTaxonomy extends MycGetterBase
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

    public function escapePostTaxonomies()
    {
        $data = [];
        foreach ($this->args['taxonomies'] ?: [] as $taxonomy => $return) {
            $terms = get_the_terms($this->postID, $taxonomy);
            if (!is_array($terms)) {
                continue;
            }

            switch ($return) {
                case 'all':
                    $data[$taxonomy] = $this->getTermsObject($terms);
                    break;
                case 'name':
                    $data[$taxonomy] = $this->getTermsName($terms);
                    break;
                case 'link':
                    $data[$taxonomy] = $this->getTermsLink($terms);
                    break;
                case 'slug/name':
                    $data[$taxonomy] = $this->getTermsSlugName($terms);
                    break;
                case 'id/name':
                    $data[$taxonomy] = $this->getTermsIDName($terms);
                    break;
                default:
                    $data[$taxonomy] = false;
                    break;
            }
        }

        return $data;
    }

    protected function getTermsObject($terms)
    {
        return apply_filters('myc_getter_get_terms_object', $terms, $this->postType, $this->postID);
    }

    protected function getTermsName($terms)
    {
        $escapedTerms = array_map(function ($term) {
            return esc_html($term->name);
        }, $terms);

        return apply_filters('myc_getter_get_terms_name', $escapedTerms, $this->postType, $this->postID);
    }

    protected function getTermsLink($terms)
    {
        $escapedTerms = array_map(function ($term) {
            return [
                'name'  => esc_html($term->name),
                'url'   => esc_url(get_term_link($term)),
            ];
        }, $terms);

        return apply_filters('myc_getter_get_terms_link', $escapedTerms, $this->postType, $this->postID);
    }

    protected function getTermsSlugName($terms)
    {
        $escapedTerms = array_map(function ($term) {
            return [
                'slug'  => esc_attr($term->slug),
                'name'  => esc_html($term->name),
            ];
        }, $terms);

        return apply_filters('myc_getter_get_terms_slug_name', $escapedTerms, $this->postType, $this->postID);
    }

    protected function getTermsIDName($terms)
    {
        $escapedTerms = array_map(function ($term) {
            return [
                'id'    => absint($term->term_id),
                'name'  => esc_html($term->name),
            ];
        }, $terms);

        return apply_filters('myc_getter_get_terms_id_name', $escapedTerms, $this->postType, $this->postID);
    }
}
