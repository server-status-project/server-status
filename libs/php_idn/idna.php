<?php
/**
 * Finds the character code for a UTF-8 character: like ord() but for UTF-8.
 *
 * @author Nicolas Thouvenin <nthouvenin@gmail.com>
 * @copyright 2008 Nicolas Thouvenin
 * @license http://opensource.org/licenses/LGPL-2.1 LGPL v2.1
 */
function ordUTF8($c, $index = 0, &$bytes = null)
  {
    $len = strlen($c);
    $bytes = 0;
    if ($index >= $len)
    return false;
    $h = ord($c{$index});
    if ($h <= 0x7F) {
    $bytes = 1;
    return $h;
    }
    else if ($h < 0xC2)
    return false;
    else if ($h <= 0xDF && $index < $len - 1) {
    $bytes = 2;
    return ($h & 0x1F) << 6 | (ord($c{$index + 1}) & 0x3F);
    }
    else if ($h <= 0xEF && $index < $len - 2) {
    $bytes = 3;
    return ($h & 0x0F) << 12 | (ord($c{$index + 1}) & 0x3F) << 6
    | (ord($c{$index + 2}) & 0x3F);
    }
    else if ($h <= 0xF4 && $index < $len - 3) {
    $bytes = 4;
    return ($h & 0x0F) << 18 | (ord($c{$index + 1}) & 0x3F) << 12
    | (ord($c{$index + 2}) & 0x3F) << 6
    | (ord($c{$index + 3}) & 0x3F);
    }
    else
    return false;
  }

/**
 * Encode UTF-8 domain name to IDN Punycode
 *
 * @param string $value Domain name
 * @return string Encoded Domain name
 *
 * @author Igor V Belousov <igor@belousovv.ru>
 * @copyright 2013, 2015 Igor V Belousov
 * @license http://opensource.org/licenses/LGPL-2.1 LGPL v2.1
 * @link http://belousovv.ru/myscript/phpIDN
 */
function EncodePunycodeIDN( $value )
  {
    if ( function_exists( 'idn_to_ascii' ) ) {
      return idn_to_ascii( $value );
    }

    /* search subdomains */
    $sub_domain = explode( '.', $value );
    if ( count( $sub_domain ) > 1 ) {
      $sub_result = '';
      foreach ( $sub_domain as $sub_value ) {
        $sub_result .= '.' . EncodePunycodeIDN( $sub_value );
      }
      return substr( $sub_result, 1 );
    }

    /* http://tools.ietf.org/html/rfc3492#section-6.3 */
    $n      = 0x80;
    $delta  = 0;
    $bias   = 72;
    $output = array();

    $input  = array();
    $str    = $value;
    while ( mb_strlen( $str , 'UTF-8' ) > 0 )
      {
        array_push( $input, mb_substr( $str, 0, 1, 'UTF-8' ) );
        $str = (version_compare(PHP_VERSION, '5.4.8','<'))?mb_substr( $str, 1, mb_strlen($str, 'UTF-8') , 'UTF-8' ):mb_substr( $str, 1, null, 'UTF-8' );
      }

    /* basic symbols */
    $basic = preg_grep( '/[\x00-\x7f]/', $input );
    $b = $basic;

    if ( $b == $input )
      {
        return $value;
      }
    $b = count( $b );
    if ( $b > 0 ) {
      $output = $basic;
      /* add delimeter */
      $output[] = '-';
    }
    unset($basic);
    /* add prefix */
    array_unshift( $output, 'xn--' );

    $input_len = count( $input );
    $h = $b;

    $ord_input = array();

    while ( $h < $input_len ) {
      $m = 0x10FFFF;
      for ( $i = 0; $i < $input_len; ++$i )
        {
          $ord_input[ $i ] = ordUtf8( $input[ $i ] );
          if ( ( $ord_input[ $i ] >= $n ) && ( $ord_input[ $i ] < $m ) )
            {
              $m = $ord_input[ $i ];
            }
        }
      if ( ( $m - $n ) > ( 0x10FFFF / ( $h + 1 ) ) )
        {
          return $value;
        }
      $delta += ( $m - $n ) * ( $h + 1 );
      $n = $m;

      for ( $i = 0; $i < $input_len; ++$i )
        {
          $c = $ord_input[ $i ];
          if ( $c < $n )
            {
              ++$delta;
              if ( $delta == 0 )
                {
                  return $value;
                }
            }
          if ( $c == $n )
            {
              $q = $delta;
              for ( $k = 36;; $k += 36 )
                {
                  if ( $k <= $bias )
                    {
                      $t = 1;
                    }
                  elseif ( $k >= ( $bias + 26 ) )
                    {
                      $t = 26;
                    }
                  else
                    {
                      $t = $k - $bias;
                    }
                  if ( $q < $t )
                    {
                      break;
                    }
                    $tmp_int = $t + ( $q - $t ) % ( 36 - $t );
                  $output[] = chr( ( $tmp_int + 22 + 75 * ( $tmp_int < 26 ) ) );
                  $q = ( $q - $t ) / ( 36 - $t );
                }

              $output[] = chr( ( $q + 22 + 75 * ( $q < 26 ) ) );
              /* http://tools.ietf.org/html/rfc3492#section-6.1 */
              $delta = ( $h == $b ) ? $delta / 700 : $delta>>1;

              $delta += intval( $delta / ( $h + 1 ) );

              $k2 = 0;
              while ( $delta > 455 )
                {
                  $delta /= 35;
                  $k2 += 36;
                }
              $bias = intval( $k2 + 36 * $delta / ( $delta + 38 ) );
              /* end section-6.1 */
              $delta = 0;
              ++$h;
            }
        }
      ++$delta;
      ++$n;
    }
    return implode( '', $output );
  }

/**
 * Decode IDN Punycode to UTF-8 domain name
 *
 * @param string $value Punycode
 * @return string Domain name in UTF-8 charset
 *
 * @author Igor V Belousov <igor@belousovv.ru>
 * @copyright 2013, 2015 Igor V Belousov
 * @license http://opensource.org/licenses/LGPL-2.1 LGPL v2.1
 * @link http://belousovv.ru/myscript/phpIDN
 */
function DecodePunycodeIDN( $value )
  {
    if ( function_exists( 'idn_to_utf8' ) ) {
      return idn_to_utf8( $value );
    }

    /* search subdomains */
    $sub_domain = explode( '.', $value );
    if ( count( $sub_domain ) > 1 ) {
      $sub_result = '';
      foreach ( $sub_domain as $sub_value ) {
        $sub_result .= '.' . DecodePunycodeIDN( $sub_value );
      }
      return substr( $sub_result, 1 );
    }

    /* search prefix */
    if ( substr( $value, 0, 4 ) != 'xn--' )
      {
        return $value;
      }
    else
      {
        $bad_input = $value;
        $value = substr( $value, 4 );
      }

    $n      = 0x80;
    $i      = 0;
    $bias   = 72;
    $output = array();

    /* search delimeter */
    $d = strrpos( $value, '-' );

    if ( $d > 0 ) {
      for ( $j = 0; $j < $d; ++$j) {
        $c = $value[ $j ];
        $output[] = $c;
        if ( $c > 0x7F )
          {
            return $bad_input;
          }
      }
      ++$d;
    } else {
      $d = 0;
    }

    while ($d < strlen( $value ) )
      {
        $old_i = $i;
        $w = 1;

        for ($k = 36;; $k += 36)
          {
            if ( $d == strlen( $value ) )
              {
                return $bad_input;
              }
            $c = $value[ $d++ ];
            $c = ord( $c );

            $digit = ( $c - 48 < 10 ) ? $c - 22 :
              (
                ( $c - 65 < 26 ) ? $c - 65 :
                  (
                    ( $c - 97 < 26 ) ? $c - 97 : 36
                  )
              );
            if ( $digit > ( 0x10FFFF - $i ) / $w )
              {
                return $bad_input;
              }
            $i += $digit * $w;

            if ( $k <= $bias )
              {
                $t = 1;
              }
            elseif ( $k >= $bias + 26 )
              {
                $t = 26;
              }
            else
              {
                $t = $k - $bias;
              }
            if ( $digit < $t ) {
                break;
              }

            $w *= 36 - $t;

          }

        $delta = $i - $old_i;

        /* http://tools.ietf.org/html/rfc3492#section-6.1 */
        $delta = ( $old_i == 0 ) ? $delta/700 : $delta>>1;

        $count_output_plus_one = count( $output ) + 1;
        $delta += intval( $delta / $count_output_plus_one );

        $k2 = 0;
        while ( $delta > 455 )
          {
            $delta /= 35;
            $k2 += 36;
          }
        $bias = intval( $k2 + 36  * $delta / ( $delta + 38 ) );
        /* end section-6.1 */
        if ( $i / $count_output_plus_one > 0x10FFFF - $n )
          {
            return $bad_input;
          }
        $n += intval( $i / $count_output_plus_one );
        $i %= $count_output_plus_one;
        array_splice( $output, $i, 0,
            html_entity_decode( '&#' . $n . ';', ENT_NOQUOTES, 'UTF-8' )
         );
        ++$i;
      }

    return implode( '', $output );
  }

