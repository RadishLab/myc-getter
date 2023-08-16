<?php

namespace RadishLab\MycGetter;

class MycGetterAcf extends MycGetterBase
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

    public function escapePostAcf()
    {
        $data = [];
        foreach ($this->args['acf'] ?: [] as $fieldSlug => $type) {
            if (is_string($type)) {
                $key = explode('|', $fieldSlug)[0];
                $data[$key] = $this->escapeACFValues($fieldSlug, $type);
            }

            if (is_array($type)) {
                $subFieldsSlug = array_keys($type);
                $repeaterCount = get_post_meta($this->postID, $fieldSlug, true);
                if (!$repeaterCount) {
                    continue;
                }

                for ($i = 0; $i < $repeaterCount; $i++) {
                    foreach ($subFieldsSlug as $subFieldSlug) {
                        $completeSubFieldSlug = "{$fieldSlug}_{$i}_{$subFieldSlug}";
                        $subType = $type[$subFieldSlug];
                        $data[$fieldSlug][$i][$subFieldSlug] = $this->escapeACFValues($completeSubFieldSlug, $subType);
                    }
                }
            }
        }

        return $data;
    }

    protected function escapeACFValues($field, $type)
    {
        $value = match ($type) {
            'date'      => $this->getACFDate($field),
            'time'      => $this->getACFTime($field),
            'link'      => $this->getACFLink($field),
            'image'     => $this->getACFImage($field),
            'image_url' => $this->getACFImage($field, true),
            'int'       => $this->getACFInt($field),
            'string'    => $this->getACFString($field),
            'attr'      => $this->getACFAttribute($field),
            'text'      => $this->getACFText($field),
            'email'     => $this->getACFEmail($field),
            'raw'       => $this->getACFRaw($field)
        };

        return $value;
    }

    protected function getACFDate($fieldSlug)
    {
        $value = get_field($fieldSlug, $this->postID, false);
        if (!$value) {
            return false;
        }

        $dateFormat = get_option('date_format');
        $dateObject = \DateTime::createFromFormat('Ymd', $value);
        if (!$dateObject) {
            return false;
        }

        $data = [
            'date'      => $dateObject->format($dateFormat),
            'global'    => $dateObject->format('c'),
        ];

        return apply_filters('myc_getter_get_acf_date', $data, $this->postType, $this->postID, $fieldSlug);
    }

    protected function getACFTime($fieldSlug)
    {
        $value = get_field($fieldSlug, $this->postID, false);
        if (!$value) {
            return false;
        }

        $timeFormat = get_option('time_format');
        $timeObject = \DateTime::createFromFormat('H:i:s', $value);
        if (!$timeObject) {
            return false;
        }

        $time = $timeObject->format($timeFormat);

        return apply_filters('myc_getter_get_acf_time', $time, $this->postType, $this->postID, $fieldSlug);
    }

    protected function getACFLink($fieldSlug)
    {
        $value = get_field($fieldSlug, $this->postID);
        if (!$value) {
            return false;
        }

        if (is_string($value)) {
            $value = [
                'title'     => '',
                'url'       => $value,
                'target'    => '',
            ];
        }

        $title = esc_html($value['title']);
        $url = esc_url($value['url']);
        $target = $value['target'] ?: '';
        $rel = str_starts_with($url, get_home_url()) ? '' : 'noopener noreferrer';

        $data = [
            'title'  => $title,
            'url'    => $url,
            'target' => $target,
            'rel'    => $rel,
        ];

        return apply_filters('myc_getter_get_acf_link', $data, $this->postType, $this->postID, $fieldSlug);
    }

    protected function getACFString($fieldSlug)
    {
        $value = get_field($fieldSlug, $this->postID);
        if (!$value) {
            return false;
        }

        $data = esc_html($value);

        return apply_filters('myc_getter_get_acf_string', $data, $this->postType, $this->postID, $fieldSlug);
    }

    protected function getACFAttribute($fieldSlug)
    {
        $value = get_field($fieldSlug, $this->postID);
        if (!$value) {
            return false;
        }

        $data = esc_attr($value);

        return apply_filters('myc_getter_get_acf_attr', $data, $this->postType, $this->postID, $fieldSlug);
    }

    protected function getACFText($fieldSlug)
    {
        $value = get_field($fieldSlug, $this->postID);
        if (!$value) {
            return false;
        }

        $data = wp_kses_post($value);

        return apply_filters('myc_getter_get_acf_text', $data, $this->postType, $this->postID, $fieldSlug);
    }

    protected function getACFImage($fieldSlugImageSize, $isUrl = false)
    {
        $args = explode('|', $fieldSlugImageSize);
        $fieldSlug = $args[0];
        $imageSize = $args[1] ?? 'thumbnail';

        $imageID = get_field($fieldSlug, $this->postID, false);
        if (!$imageID) {
            return false;
        }

        $image = $isUrl
            ? esc_url(wp_get_attachment_image_url($imageID, $imageSize))
            : wp_get_attachment_image($imageID, $imageSize);

        return apply_filters('myc_getter_get_acf_image', $image, $this->postType, $this->postID, $fieldSlug);
    }

    protected function getACFInt($fieldSlug)
    {
        $value = absint(get_field($fieldSlug, $this->postID));

        return apply_filters('myc_getter_get_acf_int', $value, $this->postType, $this->postID, $fieldSlug);
    }

    protected function getACFEmail($fieldSlug)
    {
        $value = antispambot(get_field($fieldSlug, $this->postID));

        return apply_filters('myc_getter_get_acf_email', $value, $this->postType, $this->postID, $fieldSlug);
    }

    protected function getACFRaw($fieldSlug)
    {
        $value = get_field($fieldSlug, $this->postID);

        return apply_filters('myc_getter_get_acf_raw', $value, $this->postType, $this->postID, $fieldSlug);
    }
}
