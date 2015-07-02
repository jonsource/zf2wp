<?php

function wp_parse_args( $args, $defaults = '' ) {
    if ( is_object( $args ) )
        $r = get_object_vars( $args );
    elseif ( is_array( $args ) )
        $r =& $args;
    else
        wp_parse_str( $args, $r );

    if ( is_array( $defaults ) )
        return array_merge( $defaults, $r );
    return $r;
}

function wp_parse_str( $string, &$array ) {
    parse_str( $string, $array );
    if ( get_magic_quotes_gpc() )
        $array = stripslashes_deep( $array );
    /**
     * Filter the array of variables derived from a parsed string.
     *
     * @since 2.3.0
     *
     * @param array $array The array populated with variables.
     */
    //$array = apply_filters( 'wp_parse_str', $array );
}

function absint( $maybeint ) {
    return abs( intval( $maybeint ) );
}

function wp_filter_object_list( $list, $args = array(), $operator = 'and', $field = false ) {
    if ( ! is_array( $list ) )
        return array();

    $list = wp_list_filter( $list, $args, $operator );

    if ( $field )
        $list = wp_list_pluck( $list, $field );

    return $list;
}

function wp_list_filter( $list, $args = array(), $operator = 'AND' ) {
    if ( ! is_array( $list ) )
        return array();

    if ( empty( $args ) )
        return $list;

    $operator = strtoupper( $operator );
    $count = count( $args );
    $filtered = array();

    foreach ( $list as $key => $obj ) {
        $to_match = (array) $obj;

        $matched = 0;
        foreach ( $args as $m_key => $m_value ) {
            if ( array_key_exists( $m_key, $to_match ) && $m_value == $to_match[ $m_key ] )
                $matched++;
        }

        if ( ( 'AND' == $operator && $matched == $count )
            || ( 'OR' == $operator && $matched > 0 )
            || ( 'NOT' == $operator && 0 == $matched ) ) {
            $filtered[$key] = $obj;
        }
    }

    return $filtered;
}

function wp_basename( $path, $suffix = '' ) {
    return urldecode( basename( str_replace( array( '%2F', '%5C' ), '/', urlencode( $path ) ), $suffix ) );
}

function esc_sql( $data ) {
    global $wpdb;
    return $wpdb->_escape( $data );
}

function is_wp_error( $thing ) {
    return ( $thing instanceof WP_Error );
}

function remove_accents($string)
{
    if (!preg_match('/[\x80-\xff]/', $string))
        return $string;

    if (seems_utf8($string)) {
        $chars = array(
            // Decompositions for Latin-1 Supplement
            chr(194) . chr(170) => 'a', chr(194) . chr(186) => 'o',
            chr(195) . chr(128) => 'A', chr(195) . chr(129) => 'A',
            chr(195) . chr(130) => 'A', chr(195) . chr(131) => 'A',
            chr(195) . chr(132) => 'A', chr(195) . chr(133) => 'A',
            chr(195) . chr(134) => 'AE', chr(195) . chr(135) => 'C',
            chr(195) . chr(136) => 'E', chr(195) . chr(137) => 'E',
            chr(195) . chr(138) => 'E', chr(195) . chr(139) => 'E',
            chr(195) . chr(140) => 'I', chr(195) . chr(141) => 'I',
            chr(195) . chr(142) => 'I', chr(195) . chr(143) => 'I',
            chr(195) . chr(144) => 'D', chr(195) . chr(145) => 'N',
            chr(195) . chr(146) => 'O', chr(195) . chr(147) => 'O',
            chr(195) . chr(148) => 'O', chr(195) . chr(149) => 'O',
            chr(195) . chr(150) => 'O', chr(195) . chr(153) => 'U',
            chr(195) . chr(154) => 'U', chr(195) . chr(155) => 'U',
            chr(195) . chr(156) => 'U', chr(195) . chr(157) => 'Y',
            chr(195) . chr(158) => 'TH', chr(195) . chr(159) => 's',
            chr(195) . chr(160) => 'a', chr(195) . chr(161) => 'a',
            chr(195) . chr(162) => 'a', chr(195) . chr(163) => 'a',
            chr(195) . chr(164) => 'a', chr(195) . chr(165) => 'a',
            chr(195) . chr(166) => 'ae', chr(195) . chr(167) => 'c',
            chr(195) . chr(168) => 'e', chr(195) . chr(169) => 'e',
            chr(195) . chr(170) => 'e', chr(195) . chr(171) => 'e',
            chr(195) . chr(172) => 'i', chr(195) . chr(173) => 'i',
            chr(195) . chr(174) => 'i', chr(195) . chr(175) => 'i',
            chr(195) . chr(176) => 'd', chr(195) . chr(177) => 'n',
            chr(195) . chr(178) => 'o', chr(195) . chr(179) => 'o',
            chr(195) . chr(180) => 'o', chr(195) . chr(181) => 'o',
            chr(195) . chr(182) => 'o', chr(195) . chr(184) => 'o',
            chr(195) . chr(185) => 'u', chr(195) . chr(186) => 'u',
            chr(195) . chr(187) => 'u', chr(195) . chr(188) => 'u',
            chr(195) . chr(189) => 'y', chr(195) . chr(190) => 'th',
            chr(195) . chr(191) => 'y', chr(195) . chr(152) => 'O',
            // Decompositions for Latin Extended-A
            chr(196) . chr(128) => 'A', chr(196) . chr(129) => 'a',
            chr(196) . chr(130) => 'A', chr(196) . chr(131) => 'a',
            chr(196) . chr(132) => 'A', chr(196) . chr(133) => 'a',
            chr(196) . chr(134) => 'C', chr(196) . chr(135) => 'c',
            chr(196) . chr(136) => 'C', chr(196) . chr(137) => 'c',
            chr(196) . chr(138) => 'C', chr(196) . chr(139) => 'c',
            chr(196) . chr(140) => 'C', chr(196) . chr(141) => 'c',
            chr(196) . chr(142) => 'D', chr(196) . chr(143) => 'd',
            chr(196) . chr(144) => 'D', chr(196) . chr(145) => 'd',
            chr(196) . chr(146) => 'E', chr(196) . chr(147) => 'e',
            chr(196) . chr(148) => 'E', chr(196) . chr(149) => 'e',
            chr(196) . chr(150) => 'E', chr(196) . chr(151) => 'e',
            chr(196) . chr(152) => 'E', chr(196) . chr(153) => 'e',
            chr(196) . chr(154) => 'E', chr(196) . chr(155) => 'e',
            chr(196) . chr(156) => 'G', chr(196) . chr(157) => 'g',
            chr(196) . chr(158) => 'G', chr(196) . chr(159) => 'g',
            chr(196) . chr(160) => 'G', chr(196) . chr(161) => 'g',
            chr(196) . chr(162) => 'G', chr(196) . chr(163) => 'g',
            chr(196) . chr(164) => 'H', chr(196) . chr(165) => 'h',
            chr(196) . chr(166) => 'H', chr(196) . chr(167) => 'h',
            chr(196) . chr(168) => 'I', chr(196) . chr(169) => 'i',
            chr(196) . chr(170) => 'I', chr(196) . chr(171) => 'i',
            chr(196) . chr(172) => 'I', chr(196) . chr(173) => 'i',
            chr(196) . chr(174) => 'I', chr(196) . chr(175) => 'i',
            chr(196) . chr(176) => 'I', chr(196) . chr(177) => 'i',
            chr(196) . chr(178) => 'IJ', chr(196) . chr(179) => 'ij',
            chr(196) . chr(180) => 'J', chr(196) . chr(181) => 'j',
            chr(196) . chr(182) => 'K', chr(196) . chr(183) => 'k',
            chr(196) . chr(184) => 'k', chr(196) . chr(185) => 'L',
            chr(196) . chr(186) => 'l', chr(196) . chr(187) => 'L',
            chr(196) . chr(188) => 'l', chr(196) . chr(189) => 'L',
            chr(196) . chr(190) => 'l', chr(196) . chr(191) => 'L',
            chr(197) . chr(128) => 'l', chr(197) . chr(129) => 'L',
            chr(197) . chr(130) => 'l', chr(197) . chr(131) => 'N',
            chr(197) . chr(132) => 'n', chr(197) . chr(133) => 'N',
            chr(197) . chr(134) => 'n', chr(197) . chr(135) => 'N',
            chr(197) . chr(136) => 'n', chr(197) . chr(137) => 'N',
            chr(197) . chr(138) => 'n', chr(197) . chr(139) => 'N',
            chr(197) . chr(140) => 'O', chr(197) . chr(141) => 'o',
            chr(197) . chr(142) => 'O', chr(197) . chr(143) => 'o',
            chr(197) . chr(144) => 'O', chr(197) . chr(145) => 'o',
            chr(197) . chr(146) => 'OE', chr(197) . chr(147) => 'oe',
            chr(197) . chr(148) => 'R', chr(197) . chr(149) => 'r',
            chr(197) . chr(150) => 'R', chr(197) . chr(151) => 'r',
            chr(197) . chr(152) => 'R', chr(197) . chr(153) => 'r',
            chr(197) . chr(154) => 'S', chr(197) . chr(155) => 's',
            chr(197) . chr(156) => 'S', chr(197) . chr(157) => 's',
            chr(197) . chr(158) => 'S', chr(197) . chr(159) => 's',
            chr(197) . chr(160) => 'S', chr(197) . chr(161) => 's',
            chr(197) . chr(162) => 'T', chr(197) . chr(163) => 't',
            chr(197) . chr(164) => 'T', chr(197) . chr(165) => 't',
            chr(197) . chr(166) => 'T', chr(197) . chr(167) => 't',
            chr(197) . chr(168) => 'U', chr(197) . chr(169) => 'u',
            chr(197) . chr(170) => 'U', chr(197) . chr(171) => 'u',
            chr(197) . chr(172) => 'U', chr(197) . chr(173) => 'u',
            chr(197) . chr(174) => 'U', chr(197) . chr(175) => 'u',
            chr(197) . chr(176) => 'U', chr(197) . chr(177) => 'u',
            chr(197) . chr(178) => 'U', chr(197) . chr(179) => 'u',
            chr(197) . chr(180) => 'W', chr(197) . chr(181) => 'w',
            chr(197) . chr(182) => 'Y', chr(197) . chr(183) => 'y',
            chr(197) . chr(184) => 'Y', chr(197) . chr(185) => 'Z',
            chr(197) . chr(186) => 'z', chr(197) . chr(187) => 'Z',
            chr(197) . chr(188) => 'z', chr(197) . chr(189) => 'Z',
            chr(197) . chr(190) => 'z', chr(197) . chr(191) => 's',
            // Decompositions for Latin Extended-B
            chr(200) . chr(152) => 'S', chr(200) . chr(153) => 's',
            chr(200) . chr(154) => 'T', chr(200) . chr(155) => 't',
            // Euro Sign
            chr(226) . chr(130) . chr(172) => 'E',
            // GBP (Pound) Sign
            chr(194) . chr(163) => '',
            // Vowels with diacritic (Vietnamese)
            // unmarked
            chr(198) . chr(160) => 'O', chr(198) . chr(161) => 'o',
            chr(198) . chr(175) => 'U', chr(198) . chr(176) => 'u',
            // grave accent
            chr(225) . chr(186) . chr(166) => 'A', chr(225) . chr(186) . chr(167) => 'a',
            chr(225) . chr(186) . chr(176) => 'A', chr(225) . chr(186) . chr(177) => 'a',
            chr(225) . chr(187) . chr(128) => 'E', chr(225) . chr(187) . chr(129) => 'e',
            chr(225) . chr(187) . chr(146) => 'O', chr(225) . chr(187) . chr(147) => 'o',
            chr(225) . chr(187) . chr(156) => 'O', chr(225) . chr(187) . chr(157) => 'o',
            chr(225) . chr(187) . chr(170) => 'U', chr(225) . chr(187) . chr(171) => 'u',
            chr(225) . chr(187) . chr(178) => 'Y', chr(225) . chr(187) . chr(179) => 'y',
            // hook
            chr(225) . chr(186) . chr(162) => 'A', chr(225) . chr(186) . chr(163) => 'a',
            chr(225) . chr(186) . chr(168) => 'A', chr(225) . chr(186) . chr(169) => 'a',
            chr(225) . chr(186) . chr(178) => 'A', chr(225) . chr(186) . chr(179) => 'a',
            chr(225) . chr(186) . chr(186) => 'E', chr(225) . chr(186) . chr(187) => 'e',
            chr(225) . chr(187) . chr(130) => 'E', chr(225) . chr(187) . chr(131) => 'e',
            chr(225) . chr(187) . chr(136) => 'I', chr(225) . chr(187) . chr(137) => 'i',
            chr(225) . chr(187) . chr(142) => 'O', chr(225) . chr(187) . chr(143) => 'o',
            chr(225) . chr(187) . chr(148) => 'O', chr(225) . chr(187) . chr(149) => 'o',
            chr(225) . chr(187) . chr(158) => 'O', chr(225) . chr(187) . chr(159) => 'o',
            chr(225) . chr(187) . chr(166) => 'U', chr(225) . chr(187) . chr(167) => 'u',
            chr(225) . chr(187) . chr(172) => 'U', chr(225) . chr(187) . chr(173) => 'u',
            chr(225) . chr(187) . chr(182) => 'Y', chr(225) . chr(187) . chr(183) => 'y',
            // tilde
            chr(225) . chr(186) . chr(170) => 'A', chr(225) . chr(186) . chr(171) => 'a',
            chr(225) . chr(186) . chr(180) => 'A', chr(225) . chr(186) . chr(181) => 'a',
            chr(225) . chr(186) . chr(188) => 'E', chr(225) . chr(186) . chr(189) => 'e',
            chr(225) . chr(187) . chr(132) => 'E', chr(225) . chr(187) . chr(133) => 'e',
            chr(225) . chr(187) . chr(150) => 'O', chr(225) . chr(187) . chr(151) => 'o',
            chr(225) . chr(187) . chr(160) => 'O', chr(225) . chr(187) . chr(161) => 'o',
            chr(225) . chr(187) . chr(174) => 'U', chr(225) . chr(187) . chr(175) => 'u',
            chr(225) . chr(187) . chr(184) => 'Y', chr(225) . chr(187) . chr(185) => 'y',
            // acute accent
            chr(225) . chr(186) . chr(164) => 'A', chr(225) . chr(186) . chr(165) => 'a',
            chr(225) . chr(186) . chr(174) => 'A', chr(225) . chr(186) . chr(175) => 'a',
            chr(225) . chr(186) . chr(190) => 'E', chr(225) . chr(186) . chr(191) => 'e',
            chr(225) . chr(187) . chr(144) => 'O', chr(225) . chr(187) . chr(145) => 'o',
            chr(225) . chr(187) . chr(154) => 'O', chr(225) . chr(187) . chr(155) => 'o',
            chr(225) . chr(187) . chr(168) => 'U', chr(225) . chr(187) . chr(169) => 'u',
            // dot below
            chr(225) . chr(186) . chr(160) => 'A', chr(225) . chr(186) . chr(161) => 'a',
            chr(225) . chr(186) . chr(172) => 'A', chr(225) . chr(186) . chr(173) => 'a',
            chr(225) . chr(186) . chr(182) => 'A', chr(225) . chr(186) . chr(183) => 'a',
            chr(225) . chr(186) . chr(184) => 'E', chr(225) . chr(186) . chr(185) => 'e',
            chr(225) . chr(187) . chr(134) => 'E', chr(225) . chr(187) . chr(135) => 'e',
            chr(225) . chr(187) . chr(138) => 'I', chr(225) . chr(187) . chr(139) => 'i',
            chr(225) . chr(187) . chr(140) => 'O', chr(225) . chr(187) . chr(141) => 'o',
            chr(225) . chr(187) . chr(152) => 'O', chr(225) . chr(187) . chr(153) => 'o',
            chr(225) . chr(187) . chr(162) => 'O', chr(225) . chr(187) . chr(163) => 'o',
            chr(225) . chr(187) . chr(164) => 'U', chr(225) . chr(187) . chr(165) => 'u',
            chr(225) . chr(187) . chr(176) => 'U', chr(225) . chr(187) . chr(177) => 'u',
            chr(225) . chr(187) . chr(180) => 'Y', chr(225) . chr(187) . chr(181) => 'y',
            // Vowels with diacritic (Chinese, Hanyu Pinyin)
            chr(201) . chr(145) => 'a',
            // macron
            chr(199) . chr(149) => 'U', chr(199) . chr(150) => 'u',
            // acute accent
            chr(199) . chr(151) => 'U', chr(199) . chr(152) => 'u',
            // caron
            chr(199) . chr(141) => 'A', chr(199) . chr(142) => 'a',
            chr(199) . chr(143) => 'I', chr(199) . chr(144) => 'i',
            chr(199) . chr(145) => 'O', chr(199) . chr(146) => 'o',
            chr(199) . chr(147) => 'U', chr(199) . chr(148) => 'u',
            chr(199) . chr(153) => 'U', chr(199) . chr(154) => 'u',
            // grave accent
            chr(199) . chr(155) => 'U', chr(199) . chr(156) => 'u',
        );

        // Used for locale-specific rules
        $locale = get_locale();

        if ('de_DE' == $locale) {
            $chars[chr(195) . chr(132)] = 'Ae';
            $chars[chr(195) . chr(164)] = 'ae';
            $chars[chr(195) . chr(150)] = 'Oe';
            $chars[chr(195) . chr(182)] = 'oe';
            $chars[chr(195) . chr(156)] = 'Ue';
            $chars[chr(195) . chr(188)] = 'ue';
            $chars[chr(195) . chr(159)] = 'ss';
        } elseif ('da_DK' === $locale) {
            $chars[chr(195) . chr(134)] = 'Ae';
            $chars[chr(195) . chr(166)] = 'ae';
            $chars[chr(195) . chr(152)] = 'Oe';
            $chars[chr(195) . chr(184)] = 'oe';
            $chars[chr(195) . chr(133)] = 'Aa';
            $chars[chr(195) . chr(165)] = 'aa';
        }

        $string = strtr($string, $chars);
    } else {
        $chars = array();
        // Assume ISO-8859-1 if not UTF-8
        $chars['in'] = chr(128) . chr(131) . chr(138) . chr(142) . chr(154) . chr(158)
            . chr(159) . chr(162) . chr(165) . chr(181) . chr(192) . chr(193) . chr(194)
            . chr(195) . chr(196) . chr(197) . chr(199) . chr(200) . chr(201) . chr(202)
            . chr(203) . chr(204) . chr(205) . chr(206) . chr(207) . chr(209) . chr(210)
            . chr(211) . chr(212) . chr(213) . chr(214) . chr(216) . chr(217) . chr(218)
            . chr(219) . chr(220) . chr(221) . chr(224) . chr(225) . chr(226) . chr(227)
            . chr(228) . chr(229) . chr(231) . chr(232) . chr(233) . chr(234) . chr(235)
            . chr(236) . chr(237) . chr(238) . chr(239) . chr(241) . chr(242) . chr(243)
            . chr(244) . chr(245) . chr(246) . chr(248) . chr(249) . chr(250) . chr(251)
            . chr(252) . chr(253) . chr(255);

        $chars['out'] = "EfSZszYcYuAAAAAACEEEEIIIINOOOOOOUUUUYaaaaaaceeeeiiiinoooooouuuuyy";

        $string = strtr($string, $chars['in'], $chars['out']);
        $double_chars = array();
        $double_chars['in'] = array(chr(140), chr(156), chr(198), chr(208), chr(222), chr(223), chr(230), chr(240), chr(254));
        $double_chars['out'] = array('OE', 'oe', 'AE', 'DH', 'TH', 'ss', 'ae', 'dh', 'th');
        $string = str_replace($double_chars['in'], $double_chars['out'], $string);
    }

    return $string;
}

function sanitize_title( $title, $fallback_title = '', $context = 'save' ) {
    $raw_title = $title;

    if ( 'save' == $context )
        $title = remove_accents($title);

    /**
     * Filter a sanitized title string.
     *
     * @since 1.2.0
     *
     * @param string $title     Sanitized title.
     * @param string $raw_title The title prior to sanitization.
     * @param string $context   The context for which the title is being sanitized.
     */
    $title = apply_filters( 'sanitize_title', $title, $raw_title, $context );

    if ( '' === $title || false === $title )
        $title = $fallback_title;

    return $title;
}

function wp_list_pluck( $list, $field, $index_key = null ) {
    if ( ! $index_key ) {
        /*
         * This is simple. Could at some point wrap array_column()
         * if we knew we had an array of arrays.
         */
        foreach ( $list as $key => $value ) {
            if ( is_object( $value ) ) {
                $list[ $key ] = $value->$field;
            } else {
                $list[ $key ] = $value[ $field ];
            }
        }
        return $list;
    }

    /*
     * When index_key is not set for a particular item, push the value
     * to the end of the stack. This is how array_column() behaves.
     */
    $newlist = array();
    foreach ( $list as $value ) {
        if ( is_object( $value ) ) {
            if ( isset( $value->$index_key ) ) {
                $newlist[ $value->$index_key ] = $value->$field;
            } else {
                $newlist[] = $value->$field;
            }
        } else {
            if ( isset( $value[ $index_key ] ) ) {
                $newlist[ $value[ $index_key ] ] = $value[ $field ];
            } else {
                $newlist[] = $value[ $field ];
            }
        }
    }

    return $newlist;
}

function do_action($tag, $arg = '') {
    global $wp_filter, $wp_actions, $merged_filters, $wp_current_filter;

    if ( ! isset($wp_actions[$tag]) )
        $wp_actions[$tag] = 1;
    else
        ++$wp_actions[$tag];

    // Do 'all' actions first
    if ( isset($wp_filter['all']) ) {
        $wp_current_filter[] = $tag;
        $all_args = func_get_args();
        _wp_call_all_hook($all_args);
    }

    if ( !isset($wp_filter[$tag]) ) {
        if ( isset($wp_filter['all']) )
            array_pop($wp_current_filter);
        return;
    }

    if ( !isset($wp_filter['all']) )
        $wp_current_filter[] = $tag;

    $args = array();
    if ( is_array($arg) && 1 == count($arg) && isset($arg[0]) && is_object($arg[0]) ) // array(&$this)
        $args[] =& $arg[0];
    else
        $args[] = $arg;
    for ( $a = 2, $num = func_num_args(); $a < $num; $a++ )
        $args[] = func_get_arg($a);

    // Sort
    if ( !isset( $merged_filters[ $tag ] ) ) {
        ksort($wp_filter[$tag]);
        $merged_filters[ $tag ] = true;
    }

    reset( $wp_filter[ $tag ] );

    do {
        foreach ( (array) current($wp_filter[$tag]) as $the_ )
            if ( !is_null($the_['function']) )
                call_user_func_array($the_['function'], array_slice($args, 0, (int) $the_['accepted_args']));

    } while ( next($wp_filter[$tag]) !== false );

    array_pop($wp_current_filter);
}

function do_action_ref_array($tag, $args) {
    global $wp_filter, $wp_actions, $merged_filters, $wp_current_filter;

    if ( ! isset($wp_actions[$tag]) )
        $wp_actions[$tag] = 1;
    else
        ++$wp_actions[$tag];

    // Do 'all' actions first
    if ( isset($wp_filter['all']) ) {
        $wp_current_filter[] = $tag;
        $all_args = func_get_args();
        _wp_call_all_hook($all_args);
    }

    if ( !isset($wp_filter[$tag]) ) {
        if ( isset($wp_filter['all']) )
            array_pop($wp_current_filter);
        return;
    }

    if ( !isset($wp_filter['all']) )
        $wp_current_filter[] = $tag;

    // Sort
    if ( !isset( $merged_filters[ $tag ] ) ) {
        ksort($wp_filter[$tag]);
        $merged_filters[ $tag ] = true;
    }

    reset( $wp_filter[ $tag ] );

    do {
        foreach( (array) current($wp_filter[$tag]) as $the_ )
            if ( !is_null($the_['function']) )
                call_user_func_array($the_['function'], array_slice($args, 0, (int) $the_['accepted_args']));

    } while ( next($wp_filter[$tag]) !== false );

    array_pop($wp_current_filter);
}

function is_admin() {
    if ( isset( $GLOBALS['current_screen'] ) )
        return $GLOBALS['current_screen']->in_admin();
    elseif ( defined( 'WP_ADMIN' ) )
        return WP_ADMIN;

    return false;
}

function get_option( $option, $default = false ) {
    global $wpdb;

    $option = trim( $option );
    if ( empty( $option ) )
        return false;

    /**
     * Filter the value of an existing option before it is retrieved.
     *
     * The dynamic portion of the hook name, `$option`, refers to the option name.
     *
     * Passing a truthy value to the filter will short-circuit retrieving
     * the option value, returning the passed value instead.
     *
     * @since 1.5.0
     *
     * @param bool|mixed $pre_option Value to return instead of the option value.
     *                               Default false to skip it.
     */
    $pre = apply_filters( 'pre_option_' . $option, false );
    if ( false !== $pre )
        return $pre;

    if ( defined( 'WP_SETUP_CONFIG' ) )
        return false;

    if ( ! defined( 'WP_INSTALLING' ) ) {
        // prevent non-existent options from triggering multiple queries
        //TODO: check what this does!!!
        /*$notoptions = wp_cache_get( 'notoptions', 'options' );
        if ( isset( $notoptions[ $option ] ) ) {
            return apply_filters( 'default_option_' . $option, $default );
        }*/
        $notoptions = false;

        $alloptions = wp_load_alloptions();

        if ( isset( $alloptions[$option] ) ) {
            $value = $alloptions[$option];
        } else {
            //TODO: removed cache
            //$value = wp_cache_get( $option, 'options' );
            $value = false;

            if ( false === $value ) {
                $row = $wpdb->get_row( $wpdb->prepare( "SELECT option_value FROM $wpdb->options WHERE option_name = %s LIMIT 1", $option ) );

                // Has to be get_row instead of get_var because of funkiness with 0, false, null values
                if ( is_object( $row ) ) {
                    $value = $row->option_value;
                    wp_cache_add( $option, $value, 'options' );
                } else { // option does not exist, so we must cache its non-existence
                    if ( ! is_array( $notoptions ) ) {
                        $notoptions = array();
                    }
                    $notoptions[$option] = true;
                    //wp_cache_set( 'notoptions', $notoptions, 'options' );

                    /** This filter is documented in wp-includes/option.php */
                    return apply_filters( 'default_option_' . $option, $default );
                }
            }
        }
    } else {
        $suppress = $wpdb->suppress_errors();
        $row = $wpdb->get_row( $wpdb->prepare( "SELECT option_value FROM $wpdb->options WHERE option_name = %s LIMIT 1", $option ) );
        $wpdb->suppress_errors( $suppress );
        if ( is_object( $row ) ) {
            $value = $row->option_value;
        } else {
            /** This filter is documented in wp-includes/option.php */
            return apply_filters( 'default_option_' . $option, $default );
        }
    }

    // If home is not set use siteurl.
    if ( 'home' == $option && '' == $value )
        return get_option( 'siteurl' );

    if ( in_array( $option, array('siteurl', 'home', 'category_base', 'tag_base') ) )
        $value = untrailingslashit( $value );

    /**
     * Filter the value of an existing option.
     *
     * The dynamic portion of the hook name, `$option`, refers to the option name.
     *
     * @since 1.5.0 As 'option_' . $setting
     * @since 3.0.0
     *
     * @param mixed $value Value of the option. If stored serialized, it will be
     *                     unserialized prior to being returned.
     */
    return apply_filters( 'option_' . $option, maybe_unserialize( $value ) );
}

function apply_filters( $tag, $value ) {
    global $wp_filter, $merged_filters, $wp_current_filter;

    $args = array();

    // Do 'all' actions first.
    if ( isset($wp_filter['all']) ) {
        $wp_current_filter[] = $tag;
        $args = func_get_args();
        _wp_call_all_hook($args);
    }

    if ( !isset($wp_filter[$tag]) ) {
        if ( isset($wp_filter['all']) )
            array_pop($wp_current_filter);
        return $value;
    }

    if ( !isset($wp_filter['all']) )
        $wp_current_filter[] = $tag;

    // Sort.
    if ( !isset( $merged_filters[ $tag ] ) ) {
        ksort($wp_filter[$tag]);
        $merged_filters[ $tag ] = true;
    }

    reset( $wp_filter[ $tag ] );

    if ( empty($args) )
        $args = func_get_args();

    do {
        foreach( (array) current($wp_filter[$tag]) as $the_ )
            if ( !is_null($the_['function']) ){
                $args[1] = $value;
                $value = call_user_func_array($the_['function'], array_slice($args, 1, (int) $the_['accepted_args']));
            }

    } while ( next($wp_filter[$tag]) !== false );

    array_pop( $wp_current_filter );

    return $value;
}

function wp_load_alloptions() {
    global $wpdb;

    /*if ( !defined( 'WP_INSTALLING' ) || !is_multisite() )
        $alloptions = wp_cache_get( 'alloptions', 'options' );
    else*/
        $alloptions = false;

    if ( !$alloptions ) {
        //$suppress = $wpdb->suppress_errors();
        if ( !$alloptions_db = $wpdb->get_results( "SELECT option_name, option_value FROM $wpdb->options WHERE autoload = 'yes'" ) )
            $alloptions_db = $wpdb->get_results( "SELECT option_name, option_value FROM $wpdb->options" );
        //$wpdb->suppress_errors($suppress);
        $alloptions = array();
        foreach ( (array) $alloptions_db as $o ) {
            $alloptions[$o->option_name] = $o->option_value;
        }
        //TODO:removed cache
        /*if ( !defined( 'WP_INSTALLING' ) || !is_multisite() )
            wp_cache_add( 'alloptions', $alloptions, 'options' );*/
    }

    return $alloptions;
}

function wp_load_translations_early() {

}

function wp_debug_backtrace_summary( $ignore_class = null, $skip_frames = 0, $pretty = true ) {
    if ( version_compare( PHP_VERSION, '5.2.5', '>=' ) )
        $trace = debug_backtrace( false );
    else
        $trace = debug_backtrace();

    $caller = array();
    $check_class = ! is_null( $ignore_class );
    $skip_frames++; // skip this function

    foreach ( $trace as $call ) {
        if ( $skip_frames > 0 ) {
            $skip_frames--;
        } elseif ( isset( $call['class'] ) ) {
            if ( $check_class && $ignore_class == $call['class'] )
                continue; // Filter out calls

            $caller[] = "{$call['class']}{$call['type']}{$call['function']}";
        } else {
            if ( in_array( $call['function'], array( 'do_action', 'apply_filters' ) ) ) {
                $caller[] = "{$call['function']}('{$call['args'][0]}')";
            } elseif ( in_array( $call['function'], array( 'include', 'include_once', 'require', 'require_once' ) ) ) {
                $caller[] = $call['function'] . "('" . str_replace( array( WP_CONTENT_DIR, ABSPATH ) , '', $call['args'][0] ) . "')";
            } else {
                $caller[] = $call['function'];
            }
        }
    }
    if ( $pretty )
        return join( ', ', array_reverse( $caller ) );
    else
        return $caller;
}

function is_multisite() {
    return false;
}

function wp_using_ext_object_cache() {
    return false;
}

function apply_filters_ref_array($tag, $args) {
    global $wp_filter, $merged_filters, $wp_current_filter;

    // Do 'all' actions first
    if ( isset($wp_filter['all']) ) {
        $wp_current_filter[] = $tag;
        $all_args = func_get_args();
        _wp_call_all_hook($all_args);
    }

    if ( !isset($wp_filter[$tag]) ) {
        if ( isset($wp_filter['all']) )
            array_pop($wp_current_filter);
        return $args[0];
    }

    if ( !isset($wp_filter['all']) )
        $wp_current_filter[] = $tag;

    // Sort
    if ( !isset( $merged_filters[ $tag ] ) ) {
        ksort($wp_filter[$tag]);
        $merged_filters[ $tag ] = true;
    }

    reset( $wp_filter[ $tag ] );

    do {
        foreach( (array) current($wp_filter[$tag]) as $the_ )
            if ( !is_null($the_['function']) )
                $args[0] = call_user_func_array($the_['function'], array_slice($args, 0, (int) $the_['accepted_args']));

    } while ( next($wp_filter[$tag]) !== false );

    array_pop( $wp_current_filter );

    return $args[0];
}

function get_post_type_object( $post_type ) {
    //return new stdClass();
    return null;
    //return new \WP_Post();
}

function get_current_user_id() {
    return null;
}

function is_user_logged_in() {
    return null;
}

function get_post_stati( $args = array(), $output = 'names', $operator = 'and' ) {
    return array('draft','publish','trash');
}

function maybe_unserialize( $original ) {
    if ( is_serialized( $original ) ) // don't attempt to unserialize data that wasn't serialized going in
        return @unserialize( $original );
    return $original;
}

function is_serialized( $data, $strict = true ) {
    // if it isn't a string, it isn't serialized.
    if ( ! is_string( $data ) ) {
        return false;
    }
    $data = trim( $data );
    if ( 'N;' == $data ) {
        return true;
    }
    if ( strlen( $data ) < 4 ) {
        return false;
    }
    if ( ':' !== $data[1] ) {
        return false;
    }
    if ( $strict ) {
        $lastc = substr( $data, -1 );
        if ( ';' !== $lastc && '}' !== $lastc ) {
            return false;
        }
    } else {
        $semicolon = strpos( $data, ';' );
        $brace     = strpos( $data, '}' );
        // Either ; or } must exist.
        if ( false === $semicolon && false === $brace )
            return false;
        // But neither must be in the first X characters.
        if ( false !== $semicolon && $semicolon < 3 )
            return false;
        if ( false !== $brace && $brace < 4 )
            return false;
    }
    $token = $data[0];
    switch ( $token ) {
        case 's' :
            if ( $strict ) {
                if ( '"' !== substr( $data, -2, 1 ) ) {
                    return false;
                }
            } elseif ( false === strpos( $data, '"' ) ) {
                return false;
            }
        // or else fall through
        case 'a' :
        case 'O' :
            return (bool) preg_match( "/^{$token}:[0-9]+:/s", $data );
        case 'b' :
        case 'i' :
        case 'd' :
            $end = $strict ? '$' : '';
            return (bool) preg_match( "/^{$token}:[0-9.E-]+;$end/", $data );
    }
    return false;
}

function sanitize_key( $key ) {
    $raw_key = $key;
    $key = strtolower( $key );
    $key = preg_replace( '/[^a-z0-9_\-]/', '', $key );

    /**
     * Filter a sanitized key string.
     *
     * @since 3.0.0
     *
     * @param string $key     Sanitized key.
     * @param string $raw_key The key prior to sanitization.
     */
    return apply_filters( 'sanitize_key', $key, $raw_key );
}

function get_post( $post = null, $output = OBJECT, $filter = 'raw' ) {
    if ( empty( $post ) && isset( $GLOBALS['post'] ) )
        $post = $GLOBALS['post'];

    if ( $post instanceof WP_Post ) {
        $_post = $post;
    } elseif ( is_object( $post ) ) {
        if ( empty( $post->filter ) ) {
            $_post = sanitize_post( $post, 'raw' );
            $_post = new WP_Post( $_post );
        } elseif ( 'raw' == $post->filter ) {
            $_post = new WP_Post( $post );
        } else {
            $_post = WP_Post::get_instance( $post->ID );
        }
    } else {
        $_post = WP_Post::get_instance( $post );
    }

    if ( ! $_post )
        return null;

    $_post = $_post->filter( $filter );

    if ( $output == ARRAY_A )
        return $_post->to_array();
    elseif ( $output == ARRAY_N )
        return array_values( $_post->to_array() );

    return $_post;
}

function sanitize_post( $post, $context = 'display' ) {
    if ( is_object($post) ) {
        // Check if post already filtered for this context.
        if ( isset($post->filter) && $context == $post->filter )
            return $post;
        if ( !isset($post->ID) )
            $post->ID = 0;
        foreach ( array_keys(get_object_vars($post)) as $field )
            $post->$field = sanitize_post_field($field, $post->$field, $post->ID, $context);
        $post->filter = $context;
    } else {
        // Check if post already filtered for this context.
        if ( isset($post['filter']) && $context == $post['filter'] )
            return $post;
        if ( !isset($post['ID']) )
            $post['ID'] = 0;
        foreach ( array_keys($post) as $field )
            $post[$field] = sanitize_post_field($field, $post[$field], $post['ID'], $context);
        $post['filter'] = $context;
    }
    return $post;
}

function sanitize_post_field($field, $value, $post_id, $context) {
    $int_fields = array('ID', 'post_parent', 'menu_order');
    if ( in_array($field, $int_fields) )
        $value = (int) $value;

    // Fields which contain arrays of integers.
    $array_int_fields = array( 'ancestors' );
    if ( in_array($field, $array_int_fields) ) {
        $value = array_map( 'absint', $value);
        return $value;
    }

    if ( 'raw' == $context )
        return $value;

    $prefixed = false;
    if ( false !== strpos($field, 'post_') ) {
        $prefixed = true;
        $field_no_prefix = str_replace('post_', '', $field);
    }

    if ( 'edit' == $context ) {
        $format_to_edit = array('post_content', 'post_excerpt', 'post_title', 'post_password');

        if ( $prefixed ) {

            /**
             * Filter the value of a specific post field to edit.
             *
             * The dynamic portion of the hook name, `$field`, refers to the post
             * field name.
             *
             * @since 2.3.0
             *
             * @param mixed $value   Value of the post field.
             * @param int   $post_id Post ID.
             */
            $value = apply_filters( "edit_{$field}", $value, $post_id );

            /**
             * Filter the value of a specific post field to edit.
             *
             * The dynamic portion of the hook name, `$field_no_prefix`, refers to
             * the post field name.
             *
             * @since 2.3.0
             *
             * @param mixed $value   Value of the post field.
             * @param int   $post_id Post ID.
             */
            $value = apply_filters( "{$field_no_prefix}_edit_pre", $value, $post_id );
        } else {
            $value = apply_filters( "edit_post_{$field}", $value, $post_id );
        }

        if ( in_array($field, $format_to_edit) ) {
            if ( 'post_content' == $field )
                $value = format_to_edit($value, user_can_richedit());
            else
                $value = format_to_edit($value);
        } else {
            $value = esc_attr($value);
        }
    } elseif ( 'db' == $context ) {
        if ( $prefixed ) {

            /**
             * Filter the value of a specific post field before saving.
             *
             * The dynamic portion of the hook name, `$field`, refers to the post
             * field name.
             *
             * @since 2.3.0
             *
             * @param mixed $value Value of the post field.
             */
            $value = apply_filters( "pre_{$field}", $value );

            /**
             * Filter the value of a specific field before saving.
             *
             * The dynamic portion of the hook name, `$field_no_prefix`, refers
             * to the post field name.
             *
             * @since 2.3.0
             *
             * @param mixed $value Value of the post field.
             */
            $value = apply_filters( "{$field_no_prefix}_save_pre", $value );
        } else {
            $value = apply_filters( "pre_post_{$field}", $value );

            /**
             * Filter the value of a specific post field before saving.
             *
             * The dynamic portion of the hook name, `$field`, refers to the post
             * field name.
             *
             * @since 2.3.0
             *
             * @param mixed $value Value of the post field.
             */
            $value = apply_filters( "{$field}_pre", $value );
        }
    } else {

        // Use display filters by default.
        if ( $prefixed ) {

            /**
             * Filter the value of a specific post field for display.
             *
             * The dynamic portion of the hook name, `$field`, refers to the post
             * field name.
             *
             * @since 2.3.0
             *
             * @param mixed  $value   Value of the prefixed post field.
             * @param int    $post_id Post ID.
             * @param string $context Context for how to sanitize the field. Possible
             *                        values include 'raw', 'edit', 'db', 'display',
             *                        'attribute' and 'js'.
             */
            $value = apply_filters( $field, $value, $post_id, $context );
        } else {
            $value = apply_filters( "post_{$field}", $value, $post_id, $context );
        }
    }

    if ( 'attribute' == $context )
        $value = esc_attr($value);
    elseif ( 'js' == $context )
        $value = esc_js($value);

    return $value;
}

final class WP_Post {

    /**
     * Post ID.
     *
     * @var int
     */
    public $ID;

    /**
     * ID of post author.
     *
     * A numeric string, for compatibility reasons.
     *
     * @var string
     */
    public $post_author = 0;

    /**
     * The post's local publication time.
     *
     * @var string
     */
    public $post_date = '0000-00-00 00:00:00';

    /**
     * The post's GMT publication time.
     *
     * @var string
     */
    public $post_date_gmt = '0000-00-00 00:00:00';

    /**
     * The post's content.
     *
     * @var string
     */
    public $post_content = '';

    /**
     * The post's title.
     *
     * @var string
     */
    public $post_title = '';

    /**
     * The post's excerpt.
     *
     * @var string
     */
    public $post_excerpt = '';

    /**
     * The post's status.
     *
     * @var string
     */
    public $post_status = 'publish';

    /**
     * Whether comments are allowed.
     *
     * @var string
     */
    public $comment_status = 'open';

    /**
     * Whether pings are allowed.
     *
     * @var string
     */
    public $ping_status = 'open';

    /**
     * The post's password in plain text.
     *
     * @var string
     */
    public $post_password = '';

    /**
     * The post's slug.
     *
     * @var string
     */
    public $post_name = '';

    /**
     * URLs queued to be pinged.
     *
     * @var string
     */
    public $to_ping = '';

    /**
     * URLs that have been pinged.
     *
     * @var string
     */
    public $pinged = '';

    /**
     * The post's local modified time.
     *
     * @var string
     */
    public $post_modified = '0000-00-00 00:00:00';

    /**
     * The post's GMT modified time.
     *
     * @var string
     */
    public $post_modified_gmt = '0000-00-00 00:00:00';

    /**
     * A utility DB field for post content.
     *
     *
     * @var string
     */
    public $post_content_filtered = '';

    /**
     * ID of a post's parent post.
     *
     * @var int
     */
    public $post_parent = 0;

    /**
     * The unique identifier for a post, not necessarily a URL, used as the feed GUID.
     *
     * @var string
     */
    public $guid = '';

    /**
     * A field used for ordering posts.
     *
     * @var int
     */
    public $menu_order = 0;

    /**
     * The post's type, like post or page.
     *
     * @var string
     */
    public $post_type = 'post';

    /**
     * An attachment's mime type.
     *
     * @var string
     */
    public $post_mime_type = '';

    /**
     * Cached comment count.
     *
     * A numeric string, for compatibility reasons.
     *
     * @var string
     */
    public $comment_count = 0;

    /**
     * Stores the post object's sanitization level.
     *
     * Does not correspond to a DB field.
     *
     * @var string
     */
    public $filter;

    /**
     * Retrieve WP_Post instance.
     *
     * @static
     * @access public
     *
     * @global wpdb $wpdb
     *
     * @param int $post_id Post ID.
     * @return WP_Post|false Post object, false otherwise.
     */
    public static function get_instance( $post_id ) {
        global $wpdb;

        $post_id = (int) $post_id;
        if ( ! $post_id )
            return false;

        $_post = wp_cache_get( $post_id, 'posts' );

        if ( ! $_post ) {
            $_post = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->posts WHERE ID = %d LIMIT 1", $post_id ) );

            if ( ! $_post )
                return false;

            $_post = sanitize_post( $_post, 'raw' );
            wp_cache_add( $_post->ID, $_post, 'posts' );
        } elseif ( empty( $_post->filter ) ) {
            $_post = sanitize_post( $_post, 'raw' );
        }

        return new WP_Post( $_post );
    }

    /**
     * Constructor.
     *
     * @param WP_Post|object $post Post object.
     */
    public function __construct( $post ) {
        foreach ( get_object_vars( $post ) as $key => $value )
            $this->$key = $value;
    }

    /**
     * Isset-er.
     *
     * @param string $key Property to check if set.
     * @return bool
     */
    public function __isset( $key ) {
        if ( 'ancestors' == $key )
            return true;

        if ( 'page_template' == $key )
            return ( 'page' == $this->post_type );

        if ( 'post_category' == $key )
            return true;

        if ( 'tags_input' == $key )
            return true;

        return metadata_exists( 'post', $this->ID, $key );
    }

    /**
     * Getter.
     *
     * @param string $key Key to get.
     * @return mixed
     */
    public function __get( $key ) {
        if ( 'page_template' == $key && $this->__isset( $key ) ) {
            return get_post_meta( $this->ID, '_wp_page_template', true );
        }

        if ( 'post_category' == $key ) {
            if ( is_object_in_taxonomy( $this->post_type, 'category' ) )
                $terms = get_the_terms( $this, 'category' );

            if ( empty( $terms ) )
                return array();

            return wp_list_pluck( $terms, 'term_id' );
        }

        if ( 'tags_input' == $key ) {
            if ( is_object_in_taxonomy( $this->post_type, 'post_tag' ) )
                $terms = get_the_terms( $this, 'post_tag' );

            if ( empty( $terms ) )
                return array();

            return wp_list_pluck( $terms, 'name' );
        }

        // Rest of the values need filtering.
        if ( 'ancestors' == $key )
            $value = get_post_ancestors( $this );
        else
            $value = get_post_meta( $this->ID, $key, true );

        if ( $this->filter )
            $value = sanitize_post_field( $key, $value, $this->ID, $this->filter );

        return $value;
    }

    /**
     * {@Missing Summary}
     *
     * @param string $filter Filter.
     * @return self|array|bool|object|WP_Post
     */
    public function filter( $filter ) {
        if ( $this->filter == $filter )
            return $this;

        if ( $filter == 'raw' )
            return self::get_instance( $this->ID );

        return sanitize_post( $this, $filter );
    }

    /**
     * Convert object to array.
     *
     * @return array Object as array.
     */
    public function to_array() {
        $post = get_object_vars( $this );

        foreach ( array( 'ancestors', 'page_template', 'post_category', 'tags_input' ) as $key ) {
            if ( $this->__isset( $key ) )
                $post[ $key ] = $this->__get( $key );
        }

        return $post;
    }
}