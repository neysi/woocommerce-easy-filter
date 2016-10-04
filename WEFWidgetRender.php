<?php

class WEFWidgetRender
{
    private $id = null;
    private $taxonomy = null;
    private $show_in = array('*');
    private $number = null;
    private $hide_empty = true;
    private $label = '';
    private $type = 'checkbox';
    private $current_uri;
    private $param_prefix = 'pa_';

    public function __construct()
    {
        $this->current_uri = wef_current_uri();
    }

    public function init(Array $properties = array())
    {
        foreach ($properties as $key => $value) {
            $this->{$key} = $value;
        }
    }
    /**
     * Busca la cantidad de items por terminos
     * @param  string $tax  taxonomía
     * @param  string  $term nombre el termino
     * @return [type]       [description]
     */
    private function termsQuery($tax, $term)
    {
        $url_parts = parse_url(wef_current_uri());
        parse_str($url_parts['query'], $params);

        $prod_attr = array();
        foreach ($params as $key => $value) {

      // if (substr($key, 0 , 3)=='pa_'){
        $prod_attr[] = [
          'taxonomy' => 'pa_'.$key,
          'terms' => $value,
        ];
      // }
        }

        $taxes = array(
             'relation' => 'AND',
              array(
              'taxonomy' => $tax,
              'field' => 'slug',
              'terms' => $term,
              'operator' => 'IN',
             ),

          );

        if (is_product_category()) {
            $taxes [] = array(
         'taxonomy' => 'product_cat',
         'field' => 'slug',
         'terms' => get_query_var('product_cat'),
         'operator' => 'IN',
       );
        }

        foreach ($prod_attr as $key => $value) {
            $terms = explode(',', $value['terms']);
            if ($value['taxonomy'] != $tax) {
                $taxes [] = array(
                'taxonomy' => $value['taxonomy'],
                'field' => 'slug',
                'terms' => $terms,
                'include_children' => false,
                //  'operator'=> 'IN',
              );
            }
        }

          // print_r($taxes);
          // die();

    $args = array(
        'post_status' => 'publish',
        'post_type' => 'product',
        'tax_query' => $taxes,
    );

        $query = new WP_Query($args);
        $total = $query->post_count;

        return $total;
    }

    private function getTerms(Array $options = array())
    {
        $terms = array();
        if (taxonomy_exists($this->taxonomy)) {
            $terms = get_terms($this->taxonomy, $options);
        }

        return $terms;
    }

    private function getSelectedTerms()
    {
        $options = array(
      'hide_empty' => $this->hide_empty,
      'number' => $this->number,
      'order' => 'ASC',
      'orderby' => 'slug',
    );

        if ((bool) $this->hide_empty == true) {
            $options['hide_empty'] = true;
        }
        $terms = $this->getTerms($options);

        $final_terms = array();
        $numeric_count = 0;
        foreach ($terms as $key => $term) {
            $final_terms[] = array(
        'id' => $term->id,
        'slug' => $term->slug,
        'name' => ucwords(mb_strtolower($term->name, 'UTF-8')) ,
      );

            if (is_numeric(substr($term->name, 0, 1))) {
                ++$numeric_count;
            }
        }

        if ($numeric_count > 2) {
            foreach ($final_terms as $key => $value) {
                $final_terms[$key]['number'] = filter_var($final_terms[$key]['name'], FILTER_SANITIZE_NUMBER_FLOAT);
            }

            usort($final_terms, function ($a, $b) {
        return $a['number'] - $b['number'];
      });
        }

        return $final_terms;
    }
    /**
     * Contador de terminos
     * @param  string $param termino
     * @return int
     */
    public function termsCount($param)
    {
        $url_parts = parse_url($this->current_uri);
        parse_str($url_parts['query'], $params);

        $values = explode(',', $params[$param]);
        $total = count($values);

        return $total;
    }

    /**
     * Añade los terminos a la url
     * @param  string $key         termino clave
     * @param  string $value       termino valor
     * @param  string $remove_term termino a excluir
     * @return uri
     */
    public function mergeURI($key, $value, $remove_term = null)
    {
        $key = str_replace('pa_', '', $key);

        $url_parts = parse_url($this->current_uri);
        parse_str($url_parts['query'], $params);

        if (isset($params[$key])) {
            $params[$key] = $params[$key].','.$value;
        } else {
            $params[$key] = $value;
        }

        if ($remove_term != null) {
            $params[$key] = str_replace($remove_term, '', $params[$key]);
            if ($this->termsCount($key) == 1) {
                unset($params[$key]);
            }
        }

        $url_parts['query'] = urldecode(http_build_query($params));
        $url_parts['query'] = str_replace(',,', ',', $url_parts['query']);
        $url_parts['query'] = str_replace('=,', '=', $url_parts['query']);
        $url_parts['query'] = str_replace(',&', '&', $url_parts['query']);
        $url_parts['query'] = rtrim($url_parts['query'], ',');

        return  http_build_url($this->current_uri, $url_parts);
    }

    public function checkParams($param, $term = null)
    {
        $param = str_replace('pa_', '', $param);

        $url_parts = parse_url($this->current_uri);
        parse_str($url_parts['query'], $params);

        foreach ($params as $key => $value) {
            // if ( substr($key, 0 , 3) == $this->param_prefix ){
            if ($key == $param) {
                $terms = explode(',', $value);
                foreach ($terms as $k => $tm) {
                    if ($tm == $term) {
                        return true;
                    }
                }
            }
        // }
        }

        return false;
    }

    private function makeList($terms)
    {
        if (count($terms) < 2) {
            return '';
        }

        $title = '<h3 class="wef-title  widget-title shop-sidebar">'.$this->label.'</h3>';
        $list = '<ul class="wef-list">';
        foreach ($terms as $key => $term) {
            $exists = $this->checkParams($this->taxonomy, $term['slug']);

            $tot = $this->termsQuery($this->taxonomy, $term['slug']);

            if ($tot > 0) {
                if (!$exists) {
                    $ck = '<input type="checkbox" name="'.$term['slug'].'">';
                    $list .= '<li data-tax="'.str_replace('pa_', 'wef-', $this->taxonomy).'">'.$ck.' <a rel="nofollow" href="'.$this->mergeURI($this->taxonomy, $term['slug']).'">'.$term['name'].'('.($tot).')'.'</a></li>';
                } else {
                    $ck = '<input type="checkbox" name="'.$term['slug'].'" checked="true">';
                    $list .= '<li data-tax="'.str_replace('pa_', 'wef-', $this->taxonomy).'">'.$ck.' <a rel="nofollow" href="'.$this->mergeURI($this->taxonomy, $term['slug'], $term['slug']).'">'.$term['name'].'('.($tot).')'.'</a></li>';
                }
            }
        }

        $list .= '</ul>';

        return $title.$list;
    }

    private function render(Array $attributes = array())
    {
        $this->init($attributes);

        if ($this->show_in_category[0] == '*' or  is_product_category($this->show_in_category)) {
            $terms = $this->getSelectedTerms();
            $widget_content = call_user_func(array($this, 'make'.ucfirst($this->type)), $terms);
        } else {
            return '';
        }

        return $widget_content;
    }

    public function make($wef_widgets)
    {
        $content = '';
        foreach ($wef_widgets as $key => $data) {
            $content .=  $this->render($data);
        }

        return $content;
    }
}
