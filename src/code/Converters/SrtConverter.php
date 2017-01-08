<?php namespace Done\SubtitleConverter;

class SrtConverter implements ConverterContract {

    /**
     * Converts file content (.srt, .stl... file content) to library's "internal format"
     *
     * @param string $file_content      Content of file that will be converted
     * @return array                    Internal format
     */
    public function fileContentToInternalFormat($string)
    {
        $internal_format = [];

        $blocks = explode("\n\n", trim($string));
        foreach ($blocks as $block) {
            $lines = explode("\n", $block);
            $times = explode(' --> ', $lines[1]);

            $internal_format[] = [
                'start' => self::convertFromSrtTimeToInternal($times[0]),
                'end' => self::convertFromSrtTimeToInternal($times[1]),
                'lines' => array_slice($lines, 2),
            ];
        }

        return $internal_format;
    }

    /**
     * Convert library's "internal format" to file's content
     *
     * @param array $internal_format    Internal format
     * @return string                   Converted file content
     */
    public function internalFormatToFileContent(array $internal_format)
    {
        $output = '';

        foreach ($internal_format as $k => $row) {
            $output .= $k + 1 . "\n";
            $output .= self::internalTimeToSrt($row['start']) . ' --> ' . self::internalTimeToSrt($row['end']) . "\n";
            $output .= implode("\n", $row['lines']) . "\n";
            $output .= "\n";
        }

        $output = trim($output);

        return $output;
    }

    // ------------------------------ private --------------------------------------------------------------------------

    /**
     * Convert .srt file format to internal time format (float in seconds)
     * Example: 00:02:17,440 -> 137.44
     *
     * @param $srt_time
     *
     * @return float
     */
    private static function convertFromSrtTimeToInternal($srt_time)
    {
        $parts = explode(',', $srt_time);

        $only_seconds = strtotime("1970-01-01 {$parts[0]} UTC");
        $milliseconds = (float)('0.' . $parts[1]);

        $time = $only_seconds + $milliseconds;

        return $time;
    }

    /**
     * Convert internal time format (float in seconds) to .srt time format
     * Example: 137.44 -> 00:02:17,440
     *
     * @param float $internal_time
     *
     * @return string
     */
    private static function internalTimeToSrt($internal_time)
    {
        $parts = explode('.', $internal_time); // 1.23
        $whole = $parts[0]; // 1
        $decimal = $parts[1]; // 23

        $srt_time = gmdate("H:i:s", floor($whole)) . ',' . str_pad($decimal, 3, '0', STR_PAD_RIGHT);

        return $srt_time;
    }
}