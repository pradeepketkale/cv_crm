<?php

// BCGcode128.barcode.php=======
class BCGcode128 extends BCGBarcode1D {
    const KEYA_FNC3 = 96;
    const KEYA_FNC2 = 97;
    const KEYA_SHIFT = 98;
    const KEYA_CODEC = 99;
    const KEYA_CODEB = 100;
    const KEYA_FNC4 = 101;
    const KEYA_FNC1 = 102;

    const KEYB_FNC3 = 96;
    const KEYB_FNC2 = 97;
    const KEYB_SHIFT = 98;
    const KEYB_CODEC = 99;
    const KEYB_FNC4 = 100;
    const KEYB_CODEA = 101;
    const KEYB_FNC1 = 102;

    const KEYC_CODEB = 100;
    const KEYC_CODEA = 101;
    const KEYC_FNC1 = 102;

    const KEY_STARTA = 103;
    const KEY_STARTB = 104;
    const KEY_STARTC = 105;

    const KEY_STOP = 106;

    protected $keysA, $keysB, $keysC;
    private $starting_text;
    private $indcheck, $data, $lastTable;
    private $tilde;

    private $shift;
    private $latch;
    private $fnc;

    private $METHOD            = null; // Array of method available to create Code128 (CODE128_A, CODE128_B, CODE128_C)

    /**
     * Constructor.
     *
     * @param char $start
     */
    public function __construct($start = null) {
        parent::__construct();

        /* CODE 128 A */
        $this->keysA = ' !"#$%&\'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\\]^_';
        for ($i = 0; $i < 32; $i++) {
            $this->keysA .= chr($i);
        }

        /* CODE 128 B */
        $this->keysB = ' !"#$%&\'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\\]^_`abcdefghijklmnopqrstuvwxyz{|}~' . chr(127);

        /* CODE 128 C */
        $this->keysC = '0123456789';

        $this->code = array(
            '101111',   /* 00 */
            '111011',   /* 01 */
            '111110',   /* 02 */
            '010112',   /* 03 */
            '010211',   /* 04 */
            '020111',   /* 05 */
            '011102',   /* 06 */
            '011201',   /* 07 */
            '021101',   /* 08 */
            '110102',   /* 09 */
            '110201',   /* 10 */
            '120101',   /* 11 */
            '001121',   /* 12 */
            '011021',   /* 13 */
            '011120',   /* 14 */
            '002111',   /* 15 */
            '012011',   /* 16 */
            '012110',   /* 17 */
            '112100',   /* 18 */
            '110021',   /* 19 */
            '110120',   /* 20 */
            '102101',   /* 21 */
            '112001',   /* 22 */
            '201020',   /* 23 */
            '200111',   /* 24 */
            '210011',   /* 25 */
            '210110',   /* 26 */
            '201101',   /* 27 */
            '211001',   /* 28 */
            '211100',   /* 29 */
            '101012',   /* 30 */
            '101210',   /* 31 */
            '121010',   /* 32 */
            '000212',   /* 33 */
            '020012',   /* 34 */
            '020210',   /* 35 */
            '001202',   /* 36 */
            '021002',   /* 37 */
            '021200',   /* 38 */
            '100202',   /* 39 */
            '120002',   /* 40 */
            '120200',   /* 41 */
            '001022',   /* 42 */
            '001220',   /* 43 */
            '021020',   /* 44 */
            '002012',   /* 45 */
            '002210',   /* 46 */
            '022010',   /* 47 */
            '202010',   /* 48 */
            '100220',   /* 49 */
            '120020',   /* 50 */
            '102002',   /* 51 */
            '102200',   /* 52 */
            '102020',   /* 53 */
            '200012',   /* 54 */
            '200210',   /* 55 */
            '220010',   /* 56 */
            '201002',   /* 57 */
            '201200',   /* 58 */
            '221000',   /* 59 */
            '203000',   /* 60 */
            '110300',   /* 61 */
            '320000',   /* 62 */
            '000113',   /* 63 */
            '000311',   /* 64 */
            '010013',   /* 65 */
            '010310',   /* 66 */
            '030011',   /* 67 */
            '030110',   /* 68 */
            '001103',   /* 69 */
            '001301',   /* 70 */
            '011003',   /* 71 */
            '011300',   /* 72 */
            '031001',   /* 73 */
            '031100',   /* 74 */
            '130100',   /* 75 */
            '110003',   /* 76 */
            '302000',   /* 77 */
            '130001',   /* 78 */
            '023000',   /* 79 */
            '000131',   /* 80 */
            '010031',   /* 81 */
            '010130',   /* 82 */
            '003101',   /* 83 */
            '013001',   /* 84 */
            '013100',   /* 85 */
            '300101',   /* 86 */
            '310001',   /* 87 */
            '310100',   /* 88 */
            '101030',   /* 89 */
            '103010',   /* 90 */
            '301010',   /* 91 */
            '000032',   /* 92 */
            '000230',   /* 93 */
            '020030',   /* 94 */
            '003002',   /* 95 */
            '003200',   /* 96 */
            '300002',   /* 97 */
            '300200',   /* 98 */
            '002030',   /* 99 */
            '003020',   /* 100*/
            '200030',   /* 101*/
            '300020',   /* 102*/
            '100301',   /* 103*/
            '100103',   /* 104*/
            '100121',   /* 105*/
            '122000'    /*STOP*/
        );
        $this->setStart($start);
        $this->setTilde(true);

        // Latches and Shifts
        $this->latch = array(
            array(null,             self::KEYA_CODEB,   self::KEYA_CODEC),
            array(self::KEYB_CODEA, null,               self::KEYB_CODEC),
            array(self::KEYC_CODEA, self::KEYC_CODEB,   null)
        );
        $this->shift = array(
            array(null,             self::KEYA_SHIFT),
            array(self::KEYB_SHIFT, null)
        );
        $this->fnc = array(
            array(self::KEYA_FNC1,  self::KEYA_FNC2,    self::KEYA_FNC3,    self::KEYA_FNC4),
            array(self::KEYB_FNC1,  self::KEYB_FNC2,    self::KEYB_FNC3,    self::KEYB_FNC4),
            array(self::KEYC_FNC1,  null,               null,               null)
        );

        // Method available
        $this->METHOD        = array(CODE128_A => 'A', CODE128_B => 'B', CODE128_C => 'C');
    }

    /**
     * Specifies the start code. Can be 'A', 'B', 'C', or null
     *  - Table A: Capitals + ASCII 0-31 + punct
     *  - Table B: Capitals + LowerCase + punct
     *  - Table C: Numbers
     *
     * If null is specified, the table selection is automatically made.
     * The default is null.
     *
     * @param string $table
     */
    public function setStart($table) {
        if ($table !== 'A' && $table !== 'B' && $table !== 'C' && $table !== null) {
            throw new BCGArgumentException('The starting table must be A, B, C or null.', 'table');
        }

        $this->starting_text = $table;
    }

    /**
     * Gets the tilde.
     *
     * @return bool
     */
    public function getTilde() {
        return $this->tilde;
    }

    /**
     * Accepts tilde to be process as a special character.
     * If true, you can do this:
     *  - ~~     : to make ONE tilde
     *  - ~Fx    : to insert FCNx. x is equal from 1 to 4.
     *
     * @param boolean $accept
     */
    public function setTilde($accept) {
        $this->tilde = (bool)$accept;
    }

    /**
     * Parses the text before displaying it.
     *
     * @param mixed $text
     */
    public function parse($text) {
        $this->setStartFromText($text);

        $this->text = '';
        $seq = '';

        $currentMode = $this->starting_text;

        // Here, we format correctly what the user gives.
        if (!is_array($text)) {
            $seq = $this->getSequence($text, $currentMode);
            $this->text = $text;
        } else {
            // This loop checks for UnknownText AND raises an exception if a character is not allowed in a table
            reset($text);
            while (list($key1, $val1) = each($text)) {     // We take each value
                if (!is_array($val1)) {                    // This is not a table
                    if (is_string($val1)) {                // If it's a string, parse as unknown
                        $seq .= $this->getSequence($val1, $currentMode);
                        $this->text .= $val1;
                    } else {
                        // it's the case of "array(ENCODING, 'text')"
                        // We got ENCODING in $val1, calling 'each' again will get 'text' in $val2
                        list($key2, $val2) = each($text);
                        $seq .= $this->{'setParse' . $this->METHOD[$val1]}($val2, $currentMode);
                        $this->text .= $val2;
                    }
                } else {                        // The method is specified
                    // $val1[0] = ENCODING
                    // $val1[1] = 'text'
                    $value = isset($val1[1]) ? $val1[1] : '';    // If data available
                    $seq .= $this->{'setParse' . $this->METHOD[$val1[0]]}($value, $currentMode);
                    $this->text .= $value;
                }
            }
        }

        if ($seq !== '') {
            $bitstream = $this->createBinaryStream($this->text, $seq);
            $this->setData($bitstream);
        }

        $this->addDefaultLabel();
    }

    /**
     * Draws the barcode.
     *
     * @param resource $im
     */
    public function draw($im) {

        $c = count($this->data);
        for ($i = 0; $i < $c; $i++) {
            $this->drawChar($im, $this->data[$i], true);
        }

        $this->drawChar($im, '1', true);

// $this->drawText($im, 0, 0, $this->positionX, $this->thickness);
    }

    /**
     * Returns the maximal size of a barcode.
     *
     * @param int $w
     * @param int $h
     * @return int[]
     */
    public function getDimension($w, $h) {
        // muzaffar : Contains start + text + checksum + stop

        $textlength = count($this->data) * 11;
// $endlength = 2;
        $endlength = 2; // + final bar

        $w += $textlength + $endlength;
        $h += $this->thickness;
        return parent::getDimension($w, $h);
    }

    /**
     * Validates the input.
     */
    protected function validate() {
        $c = count($this->data);
        if ($c === 0) {
            throw new BCGParseException('code128', 'No data has been entered.');
        }

        parent::validate();
    }

    /**
     * Overloaded method to calculate checksum.
     */
    protected function calculateChecksum() {
        // Checksum
        // First Char (START)
        // + Starting with the first data character following the start character,
        // take the value of the character (between 0 and 102, inclusive) multiply
        // it by its character position (1) and add that to the running checksum.
        // Modulated 103
        $this->checksumValue = $this->indcheck[0];
        $c = count($this->indcheck);
        for ($i = 1; $i < $c; $i++) {
            $this->checksumValue += $this->indcheck[$i] * $i;
        }

        $this->checksumValue = $this->checksumValue % 103;
    }

    /**
     * Overloaded method to display the checksum.
     */
    protected function processChecksum() {
        if ($this->checksumValue === false) { // Calculate the checksum only once
            $this->calculateChecksum();
        }

        if ($this->checksumValue !== false) {
            if ($this->lastTable === 'C') {
                return (string)$this->checksumValue;
            }

            return $this->{'keys' . $this->lastTable}[$this->checksumValue];
        }

        return false;
    }

    /**
     * Specifies the starting_text table if none has been specified earlier.
     *
     * @param string $text
     */
    private function setStartFromText($text) {
        if ($this->starting_text === null) {
            // If we have a forced table at the start, we get that one...
            if (is_array($text)) {
                if (is_array($text[0])) {
                    // Code like array(array(ENCODING, ''))
                    $this->starting_text = $this->METHOD[$text[0][0]];
                    return;
                } else {
                    if (is_string($text[0])) {
                        // Code like array('test') (Automatic text)
                        $text = $text[0];
                    } else {
                        // Code like array(ENCODING, '')
                        $this->starting_text = $this->METHOD[$text[0]];
                        return;
                    }
                }
            }

            // At this point, we had an "automatic" table selection...
            // If we can get at least 4 numbers, go in C; otherwise go in B.
            $tmp = preg_quote($this->keysC, '/');
            $length = strlen($text);
            if ($length >= 4 && preg_match('/[' . $tmp . ']/', substr($text, 0, 4))) {
                $this->starting_text = 'C';
            } else {
                if ($length > 0 && strpos($this->keysB, $text[0]) !== false) {
                    $this->starting_text = 'B';
                } else {
                    $this->starting_text = 'A';
                }
            }
        }
    }

    /**
     * Extracts the ~ value from the $text at the $pos.
     * If the tilde is not ~~, ~F1, ~F2, ~F3, ~F4; an error is raised.
     *
     * @param string $text
     * @param int $pos
     * @return string
     */
    private static function extractTilde($text, $pos) {
        if ($text[$pos] === '~') {
            if (isset($text[$pos + 1])) {
                // Do we have a tilde?
                if ($text[$pos + 1] === '~') {
                    return '~~';
                } elseif ($text[$pos + 1] === 'F') {
                    // Do we have a number after?
                    if (isset($text[$pos + 2])) {
                        $v = intval($text[$pos + 2]);
                        if ($v >= 1 && $v <= 4) {
                            return '~F' . $v;
                        } else {
                            throw new BCGParseException('code128', 'Bad ~F. You must provide a number from 1 to 4.');
                        }
                    } else {
                        throw new BCGParseException('code128', 'Bad ~F. You must provide a number from 1 to 4.');
                    }
                } else {
                    throw new BCGParseException('code128', 'Wrong code after the ~.');
                }
            } else {
                throw new BCGParseException('code128', 'Wrong code after the ~.');
            }
        } else {
            throw new BCGParseException('code128', 'There is no ~ at this location.');
        }
    }

    /**
     * Gets the "dotted" sequence for the $text based on the $currentMode.
     * There is also a check if we use the special tilde ~
     *
     * @param string $text
     * @param string $currentMode
     * @return string
     */
    private function getSequenceParsed($text, $currentMode) {
        if ($this->tilde) {
            $sequence = '';
            $previousPos = 0;
            while (($pos = strpos($text, '~', $previousPos)) !== false) {
                $tildeData = self::extractTilde($text, $pos);

                $simpleTilde = ($tildeData === '~~');
                if ($simpleTilde && $currentMode !== 'B') {
                    throw new BCGParseException('code128', 'The Table ' . $currentMode . ' doesn\'t contain the character ~.');
                }

                // At this point, we know we have ~Fx
                if ($tildeData !== '~F1' && $currentMode === 'C') {
                    // The mode C doesn't support ~F2, ~F3, ~F4
                    throw new BCGParseException('code128', 'The Table C doesn\'t contain the function ' . $tildeData . '.');
                }

                $length = $pos - $previousPos;
                if ($currentMode === 'C') {
                    if ($length % 2 === 1) {
                        throw new BCGParseException('code128', 'The text "' . $text . '" must have an even number of character to be encoded in Table C.');
                    }
                }

                $sequence .= str_repeat('.', $length);
                $sequence .= '.';
                $sequence .= (!$simpleTilde) ? 'F' : '';
                $previousPos = $pos + strlen($tildeData);
            }

            // Flushing
            $length = strlen($text) - $previousPos;
            if ($currentMode === 'C') {
                if ($length % 2 === 1) {
                    throw new BCGParseException('code128', 'The text "' . $text . '" must have an even number of character to be encoded in Table C.');
                }
            }

            $sequence .= str_repeat('.', $length);

            return $sequence;
        } else {
            return str_repeat('.', strlen($text));
        }
    }

    /**
     * Parses the text and returns the appropriate sequence for the Table A.
     *
     * @param string $text
     * @param string $currentMode
     * @return string
     */
    private function setParseA($text, &$currentMode) {
        $tmp = preg_quote($this->keysA, '/');

        // If we accept the ~ for special character, we must allow it.
        if ($this->tilde) {
            $tmp .= '~';
        }

        $match = array();
        if (preg_match('/[^' . $tmp . ']/', $text, $match) === 1) {
            // We found something not allowed
            throw new BCGParseException('code128', 'The text "' . $text . '" can\'t be parsed with the Table A. The character "' . $match[0] . '" is not allowed.');
        } else {
            $latch = ($currentMode === 'A') ? '' : '0';
            $currentMode = 'A';

            return $latch . $this->getSequenceParsed($text, $currentMode);
        }
    }

    /**
     * Parses the text and returns the appropriate sequence for the Table B.
     *
     * @param string $text
     * @param string $currentMode
     * @return string
     */
    private function setParseB($text, &$currentMode) {
        $tmp = preg_quote($this->keysB, '/');

        $match = array();
        if (preg_match('/[^' . $tmp . ']/', $text, $match) === 1) {
            // We found something not allowed
            throw new BCGParseException('code128', 'The text "' . $text . '" can\'t be parsed with the Table B. The character "' . $match[0] . '" is not allowed.');
        } else {
            $latch = ($currentMode === 'B') ? '' : '1';
            $currentMode = 'B';

            return $latch . $this->getSequenceParsed($text, $currentMode);
        }
    }

    /**
     * Parses the text and returns the appropriate sequence for the Table C.
     *
     * @param string $text
     * @param string $currentMode
     * @return string
     */
    private function setParseC($text, &$currentMode) {
        $tmp = preg_quote($this->keysC, '/');

        // If we accept the ~ for special character, we must allow it.
        if ($this->tilde) {
            $tmp .= '~F';
        }

        $match = array();
        if (preg_match('/[^' . $tmp . ']/', $text, $match) === 1) {
            // We found something not allowed
            throw new BCGParseException('code128', 'The text "' . $text . '" can\'t be parsed with the Table C. The character "' . $match[0] . '" is not allowed.');
        } else {
            $latch = ($currentMode === 'C') ? '' : '2';
            $currentMode = 'C';

            return $latch . $this->getSequenceParsed($text, $currentMode);
        }
    }

    /**
     * Depending on the $text, it will return the correct
     * sequence to encode the text.
     *
     * @param string $text
     * @param string $starting_text
     * @return string
     */
    private function getSequence($text, &$starting_text) {
	
	
        $e = 10000;
        $latLen = array(
            array(0, 1, 1),
            array(1, 0, 1),
            array(1, 1, 0)
        );
        $shftLen = array(
            array($e, 1, $e),
            array(1, $e, $e),
            array($e, $e, $e)
        );
        $charSiz = array(2, 2, 1);

        $startA = $e;
        $startB = $e;
        $startC = $e;
        if ($starting_text === 'A') { $startA = 0; }
        if ($starting_text === 'B') { $startB = 0; }
        if ($starting_text === 'C') { $startC = 0; }

        $curLen = array($startA, $startB, $startC);
        $curSeq = array(null, null, null);

        $nextNumber = false;

        $x = 0;
        $xLen = strlen($text);
        for ($x = 0; $x < $xLen; $x++) {
            $input = $text[$x];

            // 1.
            for ($i = 0; $i < 3; $i++) {
                for ($j = 0; $j < 3; $j++) {
                    if (($curLen[$i] + $latLen[$i][$j]) < $curLen[$j]) {
                        $curLen[$j] = $curLen[$i] + $latLen[$i][$j];
                        $curSeq[$j] = $curSeq[$i] . $j;
                    }
                }
            }

            // 2.
            $nxtLen = array($e, $e, $e);
            $nxtSeq = array();

            // 3.
            $flag = false;
            $posArray = array();

            // Special case, we do have a tilde and we process them
            if ($this->tilde && $input === '~') {
                $tildeData = self::extractTilde($text, $x);

                if ($tildeData === '~~') {
                    // We simply skip a tilde
                    $posArray[] = 1;
                    $x++;
                } elseif (substr($tildeData, 0, 2) === '~F') {
                    $v = intval($tildeData[2]);
                    $posArray[] = 0;
                    $posArray[] = 1;
                    if ($v === 1) {
                        $posArray[] = 2;
                    }

                    $x += 2;
                    $flag = true;
                }
            } else {
                $pos = strpos($this->keysA, $input);
                if ($pos !== false) {
                    $posArray[] = 0;
                }

                $pos = strpos($this->keysB, $input);
                if ($pos !== false) {
                    $posArray[] = 1;
                }

                // Do we have the next char a number?? OR a ~F1
                $pos = strpos($this->keysC, $input);
                if ($nextNumber || ($pos !== false && isset($text[$x + 1]) && strpos($this->keysC, $text[$x + 1]) !== false)) {
                    $nextNumber = !$nextNumber;
                    $posArray[] = 2;
                }
            }

            $c = count($posArray);
            for ($i = 0; $i < $c; $i++) {
                if (($curLen[$posArray[$i]] + $charSiz[$posArray[$i]]) < $nxtLen[$posArray[$i]]) {
                    $nxtLen[$posArray[$i]] = $curLen[$posArray[$i]] + $charSiz[$posArray[$i]];
                    $nxtSeq[$posArray[$i]] = $curSeq[$posArray[$i]] . '.';
                }

                for ($j = 0; $j < 2; $j++) {
                    if ($j === $posArray[$i]) { continue; }
                    if (($curLen[$j] + $shftLen[$j][$posArray[$i]] + $charSiz[$posArray[$i]]) < $nxtLen[$j]) {
                        $nxtLen[$j] = $curLen[$j] + $shftLen[$j][$posArray[$i]] + $charSiz[$posArray[$i]];
                        $nxtSeq[$j] = $curSeq[$j] . chr($posArray[$i] + 65) . '.';
                    }
                }
            }

            if ($c === 0) {
                // We found an unsuported character
                throw new BCGParseException('code128', 'Character ' .  $input . ' not supported.');
            }

            if ($flag) {
                for ($i = 0; $i < 5; $i++) {
                    if (isset($nxtSeq[$i])) {
                        $nxtSeq[$i] .= 'F';
                    }
                }
            }

            // 4.
            for ($i = 0; $i < 3; $i++) {
                $curLen[$i] = $nxtLen[$i];
                if (isset($nxtSeq[$i])) {
                    $curSeq[$i] = $nxtSeq[$i];
                }
            }
        }

        // Every curLen under $e is possible but we take the smallest
        $m = $e;
        $k = -1;
        for ($i = 0; $i < 3; $i++) {
            if ($curLen[$i] < $m) {
                $k = $i;
                $m = $curLen[$i];
            }
        }

        if ($k === -1) {
            return '';
        }

        return $curSeq[$k];
    }

    /**
     * Depending on the sequence $seq given (returned from getSequence()),
     * this method will return the code stream in an array. Each char will be a
     * string of bit based on the Code 128.
     *
     * Each letter from the sequence represents bits.
     *
     * 0 to 2 are latches
     * A to B are Shift + Letter
     * . is a char in the current encoding
     *
     * @param string $text
     * @param string $seq
     * @return string[][]
     */
    private function createBinaryStream($text, $seq) {
        $c = strlen($seq);

        $data = array(); // code stream
        $indcheck = array(); // index for checksum

        $currentEncoding = 0;
        if ($this->starting_text === 'A') {
            $currentEncoding = 0;
            $indcheck[] = self::KEY_STARTA;
            $this->lastTable = 'A';
        } elseif ($this->starting_text === 'B') {
            $currentEncoding = 1;
            $indcheck[] = self::KEY_STARTB;
            $this->lastTable = 'B';
        } elseif ($this->starting_text === 'C') {
            $currentEncoding = 2;
            $indcheck[] = self::KEY_STARTC;
            $this->lastTable = 'C';
        }

        $data[] = $this->code[103 + $currentEncoding];

        $temporaryEncoding = -1;
        for ($i = 0, $counter = 0; $i < $c; $i++) {
            $input = $seq[$i];
            $inputI = intval($input);
            if ($input === '.') {
                $this->encodeChar($data, $currentEncoding, $seq, $text, $i, $counter, $indcheck);
                if ($temporaryEncoding !== -1) {
                    $currentEncoding = $temporaryEncoding;
                    $temporaryEncoding = -1;
                }
            } elseif ($input >= 'A' && $input <= 'B') {
                // We shift
                $encoding = ord($input) - 65;
                $shift = $this->shift[$currentEncoding][$encoding];
                $indcheck[] = $shift;
                $data[] = $this->code[$shift];
                if ($temporaryEncoding === -1) {
                    $temporaryEncoding = $currentEncoding;
                }

                $currentEncoding = $encoding;
            } elseif ($inputI >= 0 && $inputI < 3) {
                $temporaryEncoding = -1;

                // We latch
                $latch = $this->latch[$currentEncoding][$inputI];
                if ($latch !== null) {
                    $indcheck[] = $latch;
                    $this->lastTable = chr(65 + $inputI);
                    $data[] = $this->code[$latch];
                    $currentEncoding = $inputI;
                }
            }
        }

        return array($indcheck, $data);
    }

    /**
     * Encodes characters, base on its encoding and sequence
     *
     * @param int[] $data
     * @param int $encoding
     * @param string $seq
     * @param string $text
     * @param int $i
     * @param int $counter
     * @param int[] $indcheck
     */
    private function encodeChar(&$data, $encoding, $seq, $text, &$i, &$counter, &$indcheck) {
        if (isset($seq[$i + 1]) && $seq[$i + 1] === 'F') {
            // We have a flag !!
            if ($text[$counter + 1] === 'F') {
                $number = $text[$counter + 2];
               $fnc = $this->fnc[$encoding][$number - 1];
                $indcheck[] = $fnc;
                $data[] = $this->code[$fnc];

                // Skip F + number
                $counter += 2;
            } else {
                // Not supposed
            }

            $i++;
        } else {
            if ($encoding === 2) {
                // We take 2 numbers in the same time
				
				
                $code = (int)substr($text, $counter, 2);
                $indcheck[] = $code;
                $data[] = $this->code[$code];
                $counter++;
                $i++;
            } else {
                $keys = ($encoding === 0) ? $this->keysA : $this->keysB;
                $pos = strpos($keys, $text[$counter]);
                $indcheck[] = $pos;
                $data[] = $this->code[$pos];
            }
        }

        $counter++;
    }

    /**
     * Saves data into the classes.
     *
     * This method will save data, calculate real column number
     * (if -1 was selected), the real error level (if -1 was
     * selected)... It will add Padding to the end and generate
     * the error codes.
     *
     * @param array $data
     */
    private function setData($data) {
	
        $this->indcheck = $data[0];
        $this->data = $data[1];
		
        $this->calculateChecksum();

        $this->data[] = $this->code[$this->checksumValue];
        $this->data[] = $this->code[self::KEY_STOP];
    }
}






// BCGFont.php===============

interface BCGFont {
    public /*internal*/ function getText();
    public /*internal*/ function setText($text);
    public /*internal*/ function getRotationAngle();
    public /*internal*/ function setRotationAngle($rotationDegree);
    public /*internal*/ function getBackgroundColor();
    public /*internal*/ function setBackgroundColor($backgroundColor);
    public /*internal*/ function getForegroundColor();
    public /*internal*/ function setForegroundColor($foregroundColor);
    public /*internal*/ function getDimension();
    public /*internal*/ function draw($im, $x, $y);
}




// BCGBarcode.php==================
abstract class BCGBarcode {
    const COLOR_BG = 0;
    const COLOR_FG = 1;

    protected $colorFg, $colorBg;       // Color Foreground, Barckground
    protected $scale;                   // Scale of the graphic, default: 1
    protected $offsetX, $offsetY;       // Position where to start the drawing
    protected $labels = array();        // Array of BCGLabel
    protected $pushLabel = array(0, 0); // Push for the label, left and top

    /**
     * Constructor.
     */
    protected function __construct() {
        $this->setOffsetX(0);
        $this->setOffsetY(0);
        $this->setForegroundColor(0x000000);
        $this->setBackgroundColor(0xffffff);
        $this->setScale(1);
    }

    /**
     * Parses the text before displaying it.
     *
     * @param mixed $text
     */
    public function parse($text) {
    }

    /**
     * Gets the foreground color of the barcode.
     *
     * @return BCGColor
     */
    public function getForegroundColor() {
        return $this->colorFg;
    }

    /**
     * Sets the foreground color of the barcode. It could be a BCGColor
     * value or simply a language code (white, black, yellow...) or hex value.
     *
     * @param mixed $code
     */
    public function setForegroundColor($code) {
        if ($code instanceof BCGColor) {
            $this->colorFg = $code;
        } else {
            $this->colorFg = new BCGColor($code);
        }
    }

    /**
     * Gets the background color of the barcode.
     *
     * @return BCGColor
     */
    public function getBackgroundColor() {
        return $this->colorBg;
    }

    /**
     * Sets the background color of the barcode. It could be a BCGColor
     * value or simply a language code (white, black, yellow...) or hex value.
     *
     * @param mixed $code
     */
    public function setBackgroundColor($code) {
        if ($code instanceof BCGColor) {
            $this->colorBg = $code;
        } else {
            $this->colorBg = new BCGColor($code);
        }

        foreach ($this->labels as $label) {
            $label->setBackgroundColor($this->colorBg);
        }
    }

    /**
     * Sets the color.
     *
     * @param mixed $fg
     * @param mixed $bg
     */
    public function setColor($fg, $bg) {
        $this->setForegroundColor($fg);
        $this->setBackgroundColor($bg);
    }

    /**
     * Gets the scale of the barcode.
     *
     * @return int
     */
    public function getScale() {
        return $this->scale;
    }

    /**
     * Sets the scale of the barcode in pixel.
     * If the scale is lower than 1, an exception is raised.
     *
     * @param int $scale
     */
    public function setScale($scale) {
// muzaffar: use this to increase or decrease scale of barcode
// $scale = floatval($scale)-0.3;

         $scale = floatval($scale);
		
        if ($scale <= 0) {
            throw new BCGArgumentException('The scale must be larger than 0.', 'scale');
        }

        $this->scale = $scale;
    }

    /**
     * Abstract method that draws the barcode on the resource.
     *
     * @param resource $im
     */
    public abstract function draw($im);

    /**
     * Returns the maximal size of a barcode.
     * [0]->width
     * [1]->height
     *
     * @param int $w
     * @param int $h
     * @return int[]
     */
    public function getDimension($w, $h) {
	
        $labels = $this->getBiggestLabels(false);
		
        $pixelsAround = array(0, 0, 0, 0); // TRBL
        if (isset($labels[BCGLabel::POSITION_TOP])) {
            $dimension = $labels[BCGLabel::POSITION_TOP]->getDimension();
            $pixelsAround[0] += $dimension[1];
        }
 
        if (isset($labels[BCGLabel::POSITION_RIGHT])) {
            $dimension = $labels[BCGLabel::POSITION_RIGHT]->getDimension();
            $pixelsAround[1] += $dimension[0];
        }

        if (isset($labels[BCGLabel::POSITION_BOTTOM])) {
            $dimension = $labels[BCGLabel::POSITION_BOTTOM]->getDimension();
            $pixelsAround[2] += $dimension[1];
        }

        if (isset($labels[BCGLabel::POSITION_LEFT])) {
            $dimension = $labels[BCGLabel::POSITION_LEFT]->getDimension();
            $pixelsAround[3] += $dimension[0];
        }

        $finalW = ($w + $this->offsetX) * $this->scale;
        $finalH = ($h + $this->offsetY) * $this->scale;

        // This section will check if a top/bottom label is too big for its width and left/right too big for its height
        $reversedLabels = $this->getBiggestLabels(true);
        foreach ($reversedLabels as $label) {
            $dimension = $label->getDimension();
            $alignment = $label->getAlignment();
            if ($label->getPosition() === BCGLabel::POSITION_LEFT || $label->getPosition() === BCGLabel::POSITION_RIGHT) {
                if ($alignment === BCGLabel::ALIGN_TOP) {
                    $pixelsAround[2] = max($pixelsAround[2], $dimension[1] - $finalH);
                } elseif ($alignment === BCGLabel::ALIGN_CENTER) {
                    $temp = ceil(($dimension[1] - $finalH) / 2);
                    $pixelsAround[0] = max($pixelsAround[0], $temp);
                    $pixelsAround[2] = max($pixelsAround[2], $temp);
                } elseif ($alignment === BCGLabel::ALIGN_BOTTOM) {
                    $pixelsAround[0] = max($pixelsAround[0], $dimension[1] - $finalH);
                }
            } else {
                if ($alignment === BCGLabel::ALIGN_LEFT) {
                    $pixelsAround[1] = max($pixelsAround[1], $dimension[0] - $finalW);
                } elseif ($alignment === BCGLabel::ALIGN_CENTER) {
                    $temp = ceil(($dimension[0] - $finalW) / 2);
                    $pixelsAround[1] = max($pixelsAround[1], $temp);
                    $pixelsAround[3] = max($pixelsAround[3], $temp);
                } elseif ($alignment === BCGLabel::ALIGN_RIGHT) {
                    $pixelsAround[3] = max($pixelsAround[3], $dimension[0] - $finalW);
                }
            }
        }

        $this->pushLabel[0] = $pixelsAround[3];
        $this->pushLabel[1] = $pixelsAround[0];

        $finalW = ($w + $this->offsetX) * $this->scale + $pixelsAround[1] + $pixelsAround[3];
        $finalH = ($h + $this->offsetY) * $this->scale + $pixelsAround[0] + $pixelsAround[2];

        return array($finalW, $finalH);
    }

    /**
     * Gets the X offset.
     *
     * @return int
     */
    public function getOffsetX() {
        return $this->offsetX;
    }

    /**
     * Sets the X offset.
     *
     * @param int $offsetX
     */
    public function setOffsetX($offsetX) {
        $offsetX = intval($offsetX);
        if ($offsetX < 0) {
            throw new BCGArgumentException('The offset X must be 0 or larger.', 'offsetX');
        }

        $this->offsetX = $offsetX;
    }

    /**
     * Gets the Y offset.
     *
     * @return int
     */
    public function getOffsetY() {
        return $this->offsetY;
    }

    /**
     * Sets the Y offset.
     *
     * @param int $offsetY
     */
    public function setOffsetY($offsetY) {
        $offsetY = intval($offsetY);
        if ($offsetY < 0) {
            throw new BCGArgumentException('The offset Y must be 0 or larger.', 'offsetY');
        }

        $this->offsetY = $offsetY;
    }

    /**
     * Adds the label to the drawing.
     *
     * @param BCGLabel $label
     */
    public function addLabel(BCGLabel $label) {
        $label->setBackgroundColor($this->colorBg);
        $this->labels[] = $label;
    }

    /**
     * Removes the label from the drawing.
     *
     * @param BCGLabel $label
     */
    public function removeLabel(BCGLabel $label) {
        $remove = -1;
        $c = count($this->labels);
        for ($i = 0; $i < $c; $i++) {
            if ($this->labels[$i] === $label) {
                $remove = $i;
                break;
            }
        }

        if ($remove > -1) {
            array_splice($this->labels, $remove, 1);
        }
    }

    /**
     * Clears the labels.
     */
    public function clearLabels() {
        $this->labels = array();
    }

    /**
     * Draws the text.
     * The coordinate passed are the positions of the barcode.
     * $x1 and $y1 represent the top left corner.
     * $x2 and $y2 represent the bottom right corner.
     *
     * @param resource $im
     * @param int $x1
     * @param int $y1
     * @param int $x2
     * @param int $y2
     */

//muzaffar: remove barcode 34 character label: start : 2-06-2015
  /**
    protected function drawText($im, $x1, $y1, $x2, $y2) {
        foreach ($this->labels as $label) {
            $label->draw($im,
                ($x1 + $this->offsetX) * $this->scale + $this->pushLabel[0],
                ($y1 + $this->offsetY) * $this->scale + $this->pushLabel[1],
                ($x2 + $this->offsetX) * $this->scale + $this->pushLabel[0],
                ($y2 + $this->offsetY) * $this->scale + $this->pushLabel[1]);
        }
    }
 
**/
//muzaffar: remove barcode 34 character label: end : 2-06-2015
    /**
     * Draws 1 pixel on the resource at a specific position with a determined color.
     *
     * @param resource $im
     * @param int $x
     * @param int $y
     * @param int $color
     */
    protected function drawPixel($im, $x, $y, $color = self::COLOR_FG) {
        $xR = ($x + $this->offsetX) * $this->scale + $this->pushLabel[0];
        $yR = ($y + $this->offsetY) * $this->scale + $this->pushLabel[1];

        // We always draw a rectangle
        imagefilledrectangle($im,
            $xR,
            $yR,
            $xR + $this->scale - 1,
            $yR + $this->scale - 1,
            $this->getColor($im, $color));
    }

    /**
     * Draws an empty rectangle on the resource at a specific position with a determined color.
     *
     * @param resource $im
     * @param int $x1
     * @param int $y1
     * @param int $x2
     * @param int $y2
     * @param int $color
     */
    protected function drawRectangle($im, $x1, $y1, $x2, $y2, $color = self::COLOR_FG) {
        if ($this->scale === 1) {
            imagefilledrectangle($im,
                ($x1 + $this->offsetX) + $this->pushLabel[0],
                ($y1 + $this->offsetY) + $this->pushLabel[1],
                ($x2 + $this->offsetX) + $this->pushLabel[0],
                ($y2 + $this->offsetY) + $this->pushLabel[1],
                $this->getColor($im, $color));
        } else {
            imagefilledrectangle($im, ($x1 + $this->offsetX) * $this->scale + $this->pushLabel[0], ($y1 + $this->offsetY) * $this->scale + $this->pushLabel[1], ($x2 + $this->offsetX) * $this->scale + $this->pushLabel[0] + $this->scale - 1, ($y1 + $this->offsetY) * $this->scale + $this->pushLabel[1] + $this->scale - 1, $this->getColor($im, $color));
            imagefilledrectangle($im, ($x1 + $this->offsetX) * $this->scale + $this->pushLabel[0], ($y1 + $this->offsetY) * $this->scale + $this->pushLabel[1], ($x1 + $this->offsetX) * $this->scale + $this->pushLabel[0] + $this->scale - 1, ($y2 + $this->offsetY) * $this->scale + $this->pushLabel[1] + $this->scale - 1, $this->getColor($im, $color));
            imagefilledrectangle($im, ($x2 + $this->offsetX) * $this->scale + $this->pushLabel[0], ($y1 + $this->offsetY) * $this->scale + $this->pushLabel[1], ($x2 + $this->offsetX) * $this->scale + $this->pushLabel[0] + $this->scale - 1, ($y2 + $this->offsetY) * $this->scale + $this->pushLabel[1] + $this->scale - 1, $this->getColor($im, $color));
            imagefilledrectangle($im, ($x1 + $this->offsetX) * $this->scale + $this->pushLabel[0], ($y2 + $this->offsetY) * $this->scale + $this->pushLabel[1], ($x2 + $this->offsetX) * $this->scale + $this->pushLabel[0] + $this->scale - 1, ($y2 + $this->offsetY) * $this->scale + $this->pushLabel[1] + $this->scale - 1, $this->getColor($im, $color));
        }
    }

    /**
     * Draws a filled rectangle on the resource at a specific position with a determined color.
     *
     * @param resource $im
     * @param int $x1
     * @param int $y1
     * @param int $x2
     * @param int $y2
     * @param int $color
     */
    protected function drawFilledRectangle($im, $x1, $y1, $x2, $y2, $color = self::COLOR_FG) {
        if ($x1 > $x2) { // Swap
            $x1 ^= $x2 ^= $x1 ^= $x2;
        }

        if ($y1 > $y2) { // Swap
            $y1 ^= $y2 ^= $y1 ^= $y2;
        }

        imagefilledrectangle($im,
            ($x1 + $this->offsetX) * $this->scale + $this->pushLabel[0],
            ($y1 + $this->offsetY) * $this->scale + $this->pushLabel[1],
            ($x2 + $this->offsetX) * $this->scale + $this->pushLabel[0] + $this->scale - 1,
            ($y2 + $this->offsetY) * $this->scale + $this->pushLabel[1] + $this->scale - 1,
            $this->getColor($im, $color));


    }

    /**
     * Allocates the color based on the integer.
     *
     * @param resource $im
     * @param int $color
     * @return resource
     */
    protected function getColor($im, $color) {
        if ($color === self::COLOR_BG) {
            return $this->colorBg->allocate($im);
        } else {
            return $this->colorFg->allocate($im);
        }
    }

    /**
     * Returning the biggest label widths for LEFT/RIGHT and heights for TOP/BOTTOM.
     *
     * @param bool $reversed
     * @return BCGLabel[]
     */
    private function getBiggestLabels($reversed = false) {
        $searchLR = $reversed ? 1 : 0;
        $searchTB = $reversed ? 0 : 1;

        $labels = array();
        foreach ($this->labels as $label) {
            $position = $label->getPosition();
            if (isset($labels[$position])) {
                $savedDimension = $labels[$position]->getDimension();
                $dimension = $label->getDimension();
                if ($position === BCGLabel::POSITION_LEFT || $position === BCGLabel::POSITION_RIGHT) {
                    if ($dimension[$searchLR] > $savedDimension[$searchLR]) {
                        $labels[$position] = $label;
                    }
                } else {
                    if ($dimension[$searchTB] > $savedDimension[$searchTB]) {
                        $labels[$position] = $label;
                    }
                }
            } else {
                $labels[$position] = $label;
            }
        }

        return $labels;
    }
}

// ========BCGargumentexception.php==============

class BCGArgumentException extends Exception {
    protected $param;

    /**
     * Constructor with specific message for a parameter.
     *
     * @param string $message
     * @param string $param
     */
    public function __construct($message, $param) {
        $this->param = $param;
        parent::__construct($message, 20000);
    }
}

// bcgbarcode1d.php==================

abstract class BCGBarcode1D extends BCGBarcode {
    const SIZE_SPACING_FONT = 5;

    const AUTO_LABEL = '##!!AUTO_LABEL!!##';

    protected $thickness;       // int
    protected $keys, $code;     // string[]
    protected $positionX;       // int
    protected $font;            // BCGFont
    protected $text;            // string
    protected $checksumValue;   // int or int[]
    protected $displayChecksum; // bool
    protected $label;           // string
    protected $defaultLabel;    // BCGLabel

    /**
     * Constructor.
     */
    protected function __construct() {
        parent::__construct();

        $this->setThickness(30);

        $this->defaultLabel = new BCGLabel();
        $this->defaultLabel->setPosition(BCGLabel::POSITION_BOTTOM);
        $this->setLabel(self::AUTO_LABEL);
        $this->setFont(new BCGFontPhp(5));

        $this->text = '';
        $this->checksumValue = false;
        $this->positionX = 0;
    }

    /**
     * Gets the thickness.
     *
     * @return int
     */
    public function getThickness() {
        return $this->thickness;
    }

    /**
     * Sets the thickness.
     *
     * @param int $thickness
     */
    public function setThickness($thickness) {
        $thickness = floatval($thickness);
        if ($thickness <= 0) {
            throw new BCGArgumentException('The thickness must be larger than 0.', 'thickness');
        }

        $this->thickness = $thickness;
    }

    /**
     * Gets the label.
     * If the label was set to BCGBarcode1D::AUTO_LABEL, the label will display the value from the text parsed.
     *
     * @return string
     */
	
	
	 
    public function getLabel() {
      $label = $this->label;
		
        if ($this->label === self::AUTO_LABEL) {
            $label = $this->text;
            if ($this->displayChecksum === true && ($checksum = $this->processChecksum()) !== false) {
                $label .= $checksum;
            }
        }

        return $label;
    }

    /**
     * Sets the label.
     * You can use BCGBarcode::AUTO_LABEL to have the label automatically written based on the parsed text.
     *
     * @param string $label
     */
    public function setLabel($label) {
        $this->label = $label;
    }

    /**
     * Gets the font.
     *
     * @return BCGFont
     */
    public function getFont() {
        return $this->font;
    }

    /**
     * Sets the font.
     *
     * @param mixed $font BCGFont or int
     */
    public function setFont($font) {
        if (is_int($font)) {
            if ($font === 0) {
                $font = null;
            } else {
                $font = new BCGFontPhp($font);
            }
        }

        $this->font = $font;
    }

    /**
     * Parses the text before displaying it.
     *
     * @param mixed $text
     */
    public function parse($text) {
        $this->text = $text;
        $this->checksumValue = false; // Reset checksumValue
        $this->validate();

        parent::parse($text);

        $this->addDefaultLabel();
    }

    /**
     * Gets the checksum of a Barcode.
     * If no checksum is available, return FALSE.
     *
     * @return string
     */
    public function getChecksum() {
        return $this->processChecksum();
    }

    /**
     * Sets if the checksum is displayed with the label or not.
     * The checksum must be activated in some case to make this variable effective.
     *
     * @param boolean $displayChecksum
     */
    public function setDisplayChecksum($displayChecksum) {
        $this->displayChecksum = (bool)$displayChecksum;
    }

    /**
     * Adds the default label.
     */
    protected function addDefaultLabel() {
        $label = $this->getLabel();
        $font = $this->font;
        if ($label !== null && $label !== '' && $font !== null && $this->defaultLabel !== null) {
            $this->defaultLabel->setText($label);
            $this->defaultLabel->setFont($font);
            $this->addLabel($this->defaultLabel);
        }
    }

    /**
     * Validates the input
     */
    protected function validate() {
        // No validation in the abstract class.
    }

    /**
     * Returns the index in $keys (useful for checksum).
     *
     * @param mixed $var
     * @return mixed
     */
    protected function findIndex($var) {
        return array_search($var, $this->keys);
    }

    /**
     * Returns the code of the char (useful for drawing bars).
     *
     * @param mixed $var
     * @return string
     */
    protected function findCode($var) {
        return $this->code[$this->findIndex($var)];
    }

    /**
     * Draws all chars thanks to $code. If $startBar is true, the line begins by a space.
     * If $startBar is false, the line begins by a bar.
     *
     * @param resource $im
     * @param string $code
     * @param boolean $startBar
     */
	
	 
	 
    protected function drawChar($im, $code, $startBar = true) {
        $colors = array(BCGBarcode::COLOR_FG, BCGBarcode::COLOR_BG);
		
        $currentColor = $startBar ? 0 : 1;
        $c = strlen($code);
		 
        for ($i = 0; $i < $c; $i++) {
            for ($j = 0; $j < intval($code[$i]) + 1; $j++) {
                $this->drawSingleBar($im, $colors[$currentColor]);
                $this->nextX();
            }

            $currentColor = ($currentColor + 1) % 2;
        }
    }

    /**
     * Draws a Bar of $color depending of the resolution.
     *
     * @param resource $img
     * @param int $color
     */
	
	
    protected function drawSingleBar($im, $color) {
        $this->drawFilledRectangle($im, $this->positionX, 0, $this->positionX, $this->thickness - 1, $color);
    }

    /**
     * Moving the pointer right to write a bar.
     */
	 
	 
    protected function nextX() {
        $this->positionX++;
    }

    /**
     * Method that saves FALSE into the checksumValue. This means no checksum
     * but this method should be overriden when needed.
     */
    protected function calculateChecksum() {
        $this->checksumValue = false;
    }

    /**
     * Returns FALSE because there is no checksum. This method should be
     * overriden to return correctly the checksum in string with checksumValue.
     *
     * @return string
     */
    protected function processChecksum() {
        return false;
    }
}

// BCGColor.php======================

class BCGColor {
    protected $r, $g, $b;    // int Hexadecimal Value
    protected $transparent;

    /**
     * Save RGB value into the classes.
     *
     * There are 4 way to associate color with this classes :
     *  1. Gives 3 parameters int (R, G, B)
     *  2. Gives 1 parameter string hex value (#ff0000) (preceding with #)
     *  3. Gives 1 parameter int hex value (0xff0000)
     *  4. Gives 1 parameter string color code (white, black, orange...)
     *
     * @param mixed ...
     */
    public function __construct() {
        $args = func_get_args();
        $c = count($args);
        if ($c === 3) {
            $this->r = intval($args[0]);
            $this->g = intval($args[1]);
            $this->b = intval($args[2]);
        } elseif ($c === 1) {
            if (is_string($args[0]) && strlen($args[0]) === 7 && $args[0][0] === '#') {        // Hex Value in String
                $this->r = intval(substr($args[0], 1, 2), 16);
                $this->g = intval(substr($args[0], 3, 2), 16);
                $this->b = intval(substr($args[0], 5, 2), 16);
            } else {
                if (is_string($args[0])) {
                    $args[0] = self::getColor($args[0]);
                }

                $args[0] = intval($args[0]);
                $this->r = ($args[0] & 0xff0000) >> 16;
                $this->g = ($args[0] & 0x00ff00) >> 8;
                $this->b = ($args[0] & 0x0000ff);
            }
        } else {
            $this->r = $this->g = $this->b = 0;
        }
    }

    /**
     * Sets the color transparent.
     *
     * @param bool $transparent
     */
    public function setTransparent($transparent) {
        $this->transparent = $transparent;
    }

    /**
     * Returns Red Color.
     *
     * @return int
     */
    public function r() {
        return $this->r;
    }

    /**
     * Returns Green Color.
     *
     * @return int
     */
    public function g() {
        return $this->g;
    }

    /**
     * Returns Blue Color.
     *
     * @return int
     */
    public function b() {
        return $this->b;
    }

    /**
     * Returns the int value for PHP color.
     *
     * @param resource $im
     * @return int
     */
    public function allocate(&$im) {
        $allocated = imagecolorallocate($im, $this->r, $this->g, $this->b);
        if ($this->transparent) {
            return imagecolortransparent($im, $allocated);
        } else {
            return $allocated;
        }
    }

    /**
     * Returns class of BCGColor depending of the string color.
     *
     * If the color doens't exist, it takes the default one.
     *
     * @param string $code
     * @param string $default
     */
    public static function getColor($code, $default = 'white') {
        switch(strtolower($code)) {
            case '':
            case 'white':
                return 0xffffff;
            case 'black':
                return 0x000000;
            case 'maroon':
                return 0x800000;
            case 'red':
                return 0xff0000;
            case 'orange':
                return 0xffa500;
            case 'yellow':
                return 0xffff00;
            case 'olive':
                return 0x808000;
            case 'purple':
                return 0x800080;
            case 'fuchsia':
                return 0xff00ff;
            case 'lime':
                return 0x00ff00;
            case 'green':
                return 0x008000;
            case 'navy':
                return 0x000080;
            case 'blue':
                return 0x0000ff;
            case 'aqua':
                return 0x00ffff;
            case 'teal':
                return 0x008080;
            case 'silver':
                return 0xc0c0c0;
            case 'gray':
                return 0x808080;
            default:
                return self::getColor($default, 'white');
        }
    }
}

// BCGDraw.php==============

abstract class BCGDraw {
    protected $im;
    protected $filename;

    /**
     * Constructor.
     *
     * @param resource $im
     */
    protected function __construct($im) {
	
        $this->im = $im;
    }

    /**
     * Sets the filename.
     *
     * @param string $filename
     */
    public function setFilename($filename) {
        $this->filename = $filename;
    }

    /**
     * Method needed to draw the image based on its specification (JPG, GIF, etc.).
     */
    abstract public function draw();
}

// BCGDrawException.php============

class BCGDrawException extends Exception {
    /**
     * Constructor with specific message.
     *
     * @param string $message
     */
    public function __construct($message) {
        parent::__construct($message, 30000);
    }
}


// BCGDrawing.php==================

class BCGDrawing {
    const IMG_FORMAT_PNG = 1;
    const IMG_FORMAT_JPEG = 2;
    const IMG_FORMAT_GIF = 3;
    const IMG_FORMAT_WBMP = 4;

    private $w, $h;         // int
    private $color;         // BCGColor
    private $filename;      // char *
    private $im;            // {object}
    private $barcode;       // BCGBarcode
    private $dpi;           // float
    private $rotateDegree;  // float

    /**
     * Constructor.
     *
     * @param int $w
     * @param int $h
     * @param string filename
     * @param BCGColor $color
     */
    public function __construct($filename = null, BCGColor $color) {
        $this->im = null;
        $this->setFilename($filename);
        $this->color = $color;
        $this->dpi = null;
        $this->rotateDegree = 0.0;
    }

    /**
     * Destructor.
     */
    public function __destruct() {
        $this->destroy();
    }

    /**
     * Gets the filename.
     *
     * @return string
     */
    public function getFilename() {
        return $this->filename;
    }

    /**
     * Sets the filename.
     *
     * @param string $filaneme
     */
    public function setFilename($filename) {
        $this->filename = $filename;
    }

    /**
     * @return resource.
     */
    public function get_im() {
        return $this->im;
    }

    /**
     * Sets the image.
     *
     * @param resource $im
     */
    public function set_im($im) {
       $this->im = $im;
    }

    /**
     * Gets barcode for drawing.
     *
     * @return BCGBarcode
     */
    public function getBarcode() {
        return $this->barcode;
    }

    /**
     * Sets barcode for drawing.
     *
     * @param BCGBarcode $barcode
     */
	
	 
    public function setBarcode(BCGBarcode $barcode) {
        $this->barcode = $barcode;
    }

    /**
     * Gets the DPI for supported filetype.
     *
     * @return float
     */
    public function getDPI() {
        return $this->dpi;
    }

    /**
     * Sets the DPI for supported filetype.
     *
     * @param float $dpi
     */
    public function setDPI($dpi) {
        $this->dpi = $dpi;
    }

    /**
     * Gets the rotation angle in degree clockwise.
     *
     * @return float
     */
    public function getRotationAngle() {
        return $this->rotateDegree;
    }

    /**
     * Sets the rotation angle in degree clockwise.
     *
     * @param float $degree
     */
    public function setRotationAngle($degree) {
        $this->rotateDegree = (float)$degree;
    }

    /**
     * Draws the barcode on the image $im.
     */
	
    public function draw() {
        $size = $this->barcode->getDimension(0, 0);
		 $this->w = max(1, $size[0]);
        
		 
        $this->h = max(1, $size[1]);
        $this->init();
        $this->barcode->draw($this->im);
    }

    /**
     * Saves $im into the file (many format available).
     *
     * @param int $image_style
     * @param int $quality
     */
    public function finish($image_style = self::IMG_FORMAT_PNG, $quality = 100) {
        $drawer = null;



        $im = $this->im;
	 
        if ($this->rotateDegree > 0.0) {
            if (function_exists('imagerotate')) {
                $im = imagerotate($this->im, 360 - $this->rotateDegree, $this->color->allocate($this->im));
            } else {
                throw new BCGDrawException('The method imagerotate doesn\'t exist on your server. Do not use any rotation.');
            }
        }

        if ($image_style === self::IMG_FORMAT_PNG) {
            $drawer = new BCGDrawPNG($im);
            $drawer->setFilename($this->filename);
            $drawer->setDPI($this->dpi);
        } elseif ($image_style === self::IMG_FORMAT_JPEG) {
            $drawer = new BCGDrawJPG($im);
            $drawer->setFilename($this->filename);
            $drawer->setDPI($this->dpi);
            $drawer->setQuality($quality);
        } elseif ($image_style === self::IMG_FORMAT_GIF) {
            // Some PHP versions have a bug if passing 2nd argument as null.
            if ($this->filename === null || $this->filename === '') {
                imagegif($im);
            } else {
                imagegif($im, $this->filename);
            }
        } elseif ($image_style === self::IMG_FORMAT_WBMP) {
            imagewbmp($im, $this->filename);
        }

        if ($drawer !== null) {
            $drawer->draw();
        }
    }

    /**
     * Writes the Error on the picture.
     *
     * @param Exception $exception
     */
    public function drawException($exception) {
        $this->w = 1;
        $this->h = 1;
        $this->init();

        // Is the image big enough?
        $w = imagesx($this->im);
        $h = imagesy($this->im);

        $text = 'Error: ' . $exception->getMessage();

        $width = imagefontwidth(2) * strlen($text);
        $height = imagefontheight(2);
        if ($width > $w || $height > $h) {
            $width = max($w, $width);
            $height = max($h, $height);

            // We change the size of the image
            $newimg = imagecreatetruecolor($width, $height);
            imagefill($newimg, 0, 0, imagecolorat($this->im, 0, 0));
            imagecopy($newimg, $this->im, 0, 0, 0, 0, $w, $h);
            $this->im = $newimg;
        }

        $black = new BCGColor('black');
        imagestring($this->im, 2, 0, 0, $text, $black->allocate($this->im));
    }

    /**
     * Free the memory of PHP (called also by destructor).
     */
    public function destroy() {
        @imagedestroy($this->im);
    }

    /**
     * Init Image and color background.
     */
    private function init() {
        if ($this->im === null) {
            $this->im = imagecreatetruecolor($this->w, $this->h)
            or die('Can\'t Initialize the GD Libraty');
            imagefilledrectangle($this->im, 0, 0, $this->w - 1, $this->h - 1, $this->color->allocate($this->im));
        }
    }
}

// BCGFontFile.php============

class BCGFontFile implements BCGFont {
    const PHP_BOX_FIX = 0;

    private $path;
    private $size;
    private $text = '';
    private $foregroundColor;
    private $rotationAngle;
    private $box;
    private $boxFix;

    /**
     * Constructor.
     *
     * @param string $fontPath path to the file
     * @param int $size size in point
     */
    public function __construct($fontPath, $size) {
        if (!file_exists($fontPath)) {
            throw new BCGArgumentException('The font path is incorrect.', 'fontPath');
        }

        $this->path = $fontPath;
        $this->size = $size;
         $this->foregroundColor = new BCGColor('black');
        $this->setRotationAngle(0);
        $this->setBoxFix(self::PHP_BOX_FIX);
    }

    /**
     * Gets the text associated to the font.
     *
     * @return string
     */
    public function getText() {
        return $this->text;
    }

    /**
     * Sets the text associated to the font.
     *
     * @param string text
     */
    public function setText($text) {
        $this->text = $text;
        $this->box = null;
    }

    /**
     * Gets the rotation in degree.
     *
     * @return int
     */
    public function getRotationAngle() {
        return (360 - $this->rotationAngle) % 360;
    }

    /**
     * Sets the rotation in degree.
     *
     * @param int
     */
    public function setRotationAngle($rotationAngle) {
        $this->rotationAngle = (int)$rotationAngle;
        if ($this->rotationAngle !== 90 && $this->rotationAngle !== 180 && $this->rotationAngle !== 270) {
            $this->rotationAngle = 0;
        }

        $this->rotationAngle = (360 - $this->rotationAngle) % 360;

        $this->box = null;
    }

    /**
     * Gets the background color.
     *
     * @return BCGColor
     */
    public function getBackgroundColor() {
    }

    /**
     * Sets the background color.
     *
     * @param BCGColor $backgroundColor
     */
    public function setBackgroundColor($backgroundColor) {
    }

    /**
     * Gets the foreground color.
     *
     * @return BCGColor
     */
    public function getForegroundColor() {
        return $this->foregroundColor;
    }

    /**
     * Sets the foreground color.
     *
     * @param BCGColor $foregroundColor
     */
    public function setForegroundColor($foregroundColor) {
        $this->foregroundColor = $foregroundColor;
    }

    /**
     * Gets the box fix information.
     *
     * @return int
     */
    public function getBoxFix() {
        return $this->boxFix;
    }

    /**
     * Sets the box fix information.
     *
     * @param int $value
     */
    public function setBoxFix($value) {
        $this->boxFix = intval($value);
    }

    /**
     * Returns the width and height that the text takes to be written.
     *
     * @return int[]
     */
	
    public function getDimension() {
        $w = 0.0;
        $h = 0.0;
        $box = $this->getBox();

        if ($box !== null) {
            $minX = min(array($box[0], $box[2], $box[4], $box[6]));
            $maxX = max(array($box[0], $box[2], $box[4], $box[6]));
            $minY = min(array($box[1], $box[3], $box[5], $box[7]));
            $maxY = max(array($box[1], $box[3], $box[5], $box[7]));

             $w = $maxX - $minX;
		 
            $h = $maxY - $minY;
        }

        $rotationAngle = $this->getRotationAngle();
        if ($rotationAngle === 90 || $rotationAngle === 270) {
            return array($h + self::PHP_BOX_FIX, $w);
        } else {
            return array($w + self::PHP_BOX_FIX, $h);
        }
    }

    /**
     * Draws the text on the image at a specific position.
     * $x and $y represent the left bottom corner.
     *
     * @param resource $im
     * @param int $x
     * @param int $y
     */
	
    public function draw($im, $x, $y) {
        $drawingPosition = $this->getDrawingPosition($x, $y);
        imagettftext($im, $this->size, $this->rotationAngle, $drawingPosition[0], $drawingPosition[1], $this->foregroundColor->allocate($im), $this->path, $this->text);
    }

    private function getDrawingPosition($x, $y) {
        $dimension = $this->getDimension();
        $box = $this->getBox();
        $rotationAngle = $this->getRotationAngle();
        if ($rotationAngle === 0) {
            $y += abs(min($box[5], $box[7]));

        } elseif ($rotationAngle === 90) {
            $x += abs(min($box[5], $box[7]));
            $y += $dimension[1];
        } elseif ($rotationAngle === 180) {
            $x += $dimension[0];
            $y += abs(max($box[1], $box[3]));
        } elseif ($rotationAngle === 270) {
            $x += abs(max($box[1], $box[3]));
        }

        return array($x, $y);
    }

    private function getBox() {
        if ($this->box === null) {
           $gd = imagecreate(1, 1);
		
            $this->box = imagettftext($gd, $this->size, 0, 0, 0, 0, $this->path, $this->text);
        }

        return $this->box;
    }
}

// BCGFontPhp.php=================

class BCGFontPhp implements BCGFont {
    private $font;
    private $text;
    private $rotationAngle;
    private $backgroundColor;
    private $foregroundColor;

    /**
     * Constructor.
     *
     * @param int $font
     */
    public function __construct($font) {
        $this->font = max(0, intval($font));
        $this->backgroundColor = new BCGColor('white');
        $this->foregroundColor = new BCGColor('black');
        $this->setRotationAngle(0);
    }

    /**
     * Gets the text associated to the font.
     *
     * @return string
     */
    public function getText() {
        return $this->text;
    }

    /**
     * Sets the text associated to the font.
     *
     * @param string text
     */
    public function setText($text) {
        $this->text = $text;
    }

    /**
     * Gets the rotation in degree.
     *
     * @return int
     */
    public function getRotationAngle() {
        return (360 - $this->rotationAngle) % 360;
    }

    /**
     * Sets the rotation in degree.
     *
     * @param int
     */
    public function setRotationAngle($rotationAngle) {
        $this->rotationAngle = (int)$rotationAngle;
        if ($this->rotationAngle !== 90 && $this->rotationAngle !== 180 && $this->rotationAngle !== 270) {
            $this->rotationAngle = 0;
        }

        $this->rotationAngle = (360 - $this->rotationAngle) % 360;
    }

    /**
     * Gets the background color.
     *
     * @return BCGColor
     */
    public function getBackgroundColor() {
        return $this->backgroundColor;
    }

    /**
     * Sets the background color.
     *
     * @param BCGColor $backgroundColor
     */
    public function setBackgroundColor($backgroundColor) {
        $this->backgroundColor = $backgroundColor;
    }

    /**
     * Gets the foreground color.
     *
     * @return BCGColor
     */
    public function getForegroundColor() {
        return $this->foregroundColor;
    }

    /**
     * Sets the foreground color.
     *
     * @param BCGColor $foregroundColor
     */
    public function setForegroundColor($foregroundColor) {
        $this->foregroundColor = $foregroundColor;
    }

    /**
     * Returns the width and height that the text takes to be written.
     *
     * @return int[]
     */
    public function getDimension() {
        $w = imagefontwidth($this->font) * strlen($this->text);
		 
        $h = imagefontheight($this->font);

        $rotationAngle = $this->getRotationAngle();
        if ($rotationAngle === 90 || $rotationAngle === 270) {
            return array($h, $w);
        } else {
            return array($w, $h);
        }
    }

    /**
     * Draws the text on the image at a specific position.
     * $x and $y represent the left bottom corner.
     *
     * @param resource $im
     * @param int $x
     * @param int $y
     */
    public function draw($im, $x, $y) {
        if ($this->getRotationAngle() !== 0) {
            if (!function_exists('imagerotate')) {
                throw new BCGDrawException('The method imagerotate doesn\'t exist on your server. Do not use any rotation.');
            }

            $w = imagefontwidth($this->font) * strlen($this->text);
            $h = imagefontheight($this->font);
            $gd = imagecreatetruecolor($w, $h);
            imagefilledrectangle($gd, 0, 0, $w - 1, $h - 1, $this->backgroundColor->allocate($gd));
            imagestring($gd, $this->font, 0, 0, $this->text, $this->foregroundColor->allocate($gd));
            $gd = imagerotate($gd, $this->rotationAngle, 0);
            imagecopy($im, $gd, $x, $y, 0, 0, imagesx($gd), imagesy($gd));
        } else {
            imagestring($im, $this->font, $x, $y, $this->text, $this->foregroundColor->allocate($im));
        }
    }
}



// BCGLabel.php==============

class BCGLabel {
    const POSITION_TOP = 0;
    const POSITION_RIGHT = 1;
    const POSITION_BOTTOM = 2;
    const POSITION_LEFT = 3;

    const ALIGN_LEFT = 0;
    const ALIGN_TOP = 0;
    const ALIGN_CENTER = 1;
    const ALIGN_RIGHT = 2;
    const ALIGN_BOTTOM = 2;

    private $font;
    private $text;
    private $position;
    private $alignment;
    private $offset;
    private $spacing;
    private $rotationAngle;
    private $backgroundColor;
    private $foregroundColor;

    /**
     * Constructor.
     *
     * @param string $text
     * @param BCGFont $font
     * @param int $position
     * @param int $alignment
     */
    public function __construct($text = '', $font = null, $position = self::POSITION_BOTTOM, $alignment = self::ALIGN_CENTER) {
        $font = $font === null ? new BCGFontPhp(5) : $font;
        $this->setFont($font);
		
         $this->setText($text); 
        $this->setPosition($position);
        $this->setAlignment($alignment);
        $this->setSpacing(4);
        $this->setOffset(0);
        $this->setRotationAngle(0);
        $this->setBackgroundColor(new BCGColor('white'));
        $this->setForegroundColor(new BCGColor('black'));
    }

    /**
     * Gets the text.
     *
     * @return string
     */
    public function getText() {
        return $this->font->getText();
    }

    /**
     * Sets the text.
     *
     * @param string $text
     */
    public function setText($text) {
        $this->text = $text;
		
        $this->font->setText($this->text);
    }

    /**
     * Gets the font.
     *
     * @return BCGFont
     */
    public function getFont() {
        return $this->font;
    }

    /**
     * Sets the font.
     *
     * @param BCGFont $font
     */
    public function setFont($font) {
        if ($font === null) {
            throw new BCGArgumentException('Font cannot be null.', 'font');
        }

        $this->font = clone $font;
        $this->font->setText($this->text);
        $this->font->setRotationAngle($this->rotationAngle);
        $this->font->setBackgroundColor($this->backgroundColor);
        $this->font->setForegroundColor($this->foregroundColor);
    }

    /**
     * Gets the text position for drawing.
     *
     * @return int
     */
    public function getPosition() {
        return $this->position;
    }

    /**
     * Sets the text position for drawing.
     *
     * @param int $position
     */
    public function setPosition($position) {
        $position = intval($position);
        if ($position !== self::POSITION_TOP && $position !== self::POSITION_RIGHT && $position !== self::POSITION_BOTTOM && $position !== self::POSITION_LEFT) {
            throw new BCGArgumentException('The text position must be one of a valid constant.', 'position');
        }

        $this->position = $position;
    }

    /**
     * Gets the text alignment for drawing.
     *
     * @return int
     */
    public function getAlignment() {
        return $this->alignment;
    }

    /**
     * Sets the text alignment for drawing.
     *
     * @param int $alignment
     */
    public function setAlignment($alignment) {
        $alignment = intval($alignment);
        if ($alignment !== self::ALIGN_LEFT && $alignment !== self::ALIGN_TOP && $alignment !== self::ALIGN_CENTER && $alignment !== self::ALIGN_RIGHT && $alignment !== self::ALIGN_BOTTOM) {
            throw new BCGArgumentException('The text alignment must be one of a valid constant.', 'alignment');
        }

        $this->alignment = $alignment;
    }

    /**
     * Gets the offset.
     *
     * @return int
     */
    public function getOffset() {
        return $this->offset;
    }

    /**
     * Sets the offset.
     *
     * @param int $offset
     */
    public function setOffset($offset) {
        $this->offset = intval($offset);
    }

    /**
     * Gets the spacing.
     *
     * @return int
     */
    public function getSpacing() {
        return $this->spacing;
    }

    /**
     * Sets the spacing.
     *
     * @param int $spacing
     */
    public function setSpacing($spacing) {
        $this->spacing = max(0, intval($spacing));
    }

    /**
     * Gets the rotation angle in degree.
     *
     * @return int
     */
    public function getRotationAngle() {
        return $this->font->getRotationAngle();
    }

    /**
     * Sets the rotation angle in degree.
     *
     * @param int $rotationAngle
     */
    public function setRotationAngle($rotationAngle) {
        $this->rotationAngle = intval($rotationAngle);
        $this->font->setRotationAngle($this->rotationAngle);
    }

    /**
     * Gets the background color in case of rotation.
     *
     * @return BCGColor
     */
    public function getBackgroundColor() {
        return $this->backgroundColor;
    }

    /**
     * Sets the background color in case of rotation.
     *
     * @param BCGColor $backgroundColor
     */
    public /*internal*/ function setBackgroundColor($backgroundColor) {
        $this->backgroundColor = $backgroundColor;
        $this->font->setBackgroundColor($this->backgroundColor);
    }

    /**
     * Gets the foreground color.
     *
     * @return BCGColor
     */
    public function getForegroundColor() {
        return $this->font->getForegroundColor();
    }

    /**
     * Sets the foreground color.
     *
     * @param BCGColor $foregroundColor
     */
    public function setForegroundColor($foregroundColor) {
        $this->foregroundColor = $foregroundColor;
        $this->font->setForegroundColor($this->foregroundColor);
    }

    /**
     * Gets the dimension taken by the label, including the spacing and offset.
     * [0]: width
     * [1]: height
     *
     * @return int[]
     */
    public function getDimension() {
        $w = 0;
        $h = 0;
        $dimension = $this->font->getDimension();
		
        $w = $dimension[0];
        $h = $dimension[1];

        if ($this->position === self::POSITION_TOP || $this->position === self::POSITION_BOTTOM) {
            $h += $this->spacing;
            $w += max(0, $this->offset);
        } else {
            $w += $this->spacing;
            $h += max(0, $this->offset);
        }

        return array($w, $h);
    }

    /**
     * Draws the text.
     * The coordinate passed are the positions of the barcode.
     * $x1 and $y1 represent the top left corner.
     * $x2 and $y2 represent the bottom right corner.
     *
     * @param resource $im
     * @param int $x1
     * @param int $y1
     * @param int $x2
     * @param int $y2
     */
    public /*internal*/ function draw($im, $x1, $y1, $x2, $y2) {
        $x = 0;
        $y = 0;

        $fontDimension = $this->font->getDimension();

        if ($this->position === self::POSITION_TOP || $this->position === self::POSITION_BOTTOM) {
            if ($this->position === self::POSITION_TOP) {
                $y = $y1 - $this->spacing - $fontDimension[1];
            } elseif ($this->position === self::POSITION_BOTTOM) {
                $y = $y2 + $this->spacing;
            }

            if ($this->alignment === self::ALIGN_CENTER) {
                $x = ($x2 - $x1) / 2 + $x1 - $fontDimension[0] / 2 + $this->offset;
            } elseif ($this->alignment === self::ALIGN_LEFT)  {
                $x = $x1 + $this->offset;
            } else {
                $x = $x2 + $this->offset - $fontDimension[0];
            }
        } else {
            if ($this->position === self::POSITION_LEFT) {
                $x = $x1 - $this->spacing - $fontDimension[0];
            } elseif ($this->position === self::POSITION_RIGHT) {
                $x = $x2 + $this->spacing;
            }

            if ($this->alignment === self::ALIGN_CENTER) {
                $y = ($y2 - $y1) / 2 + $y1 - $fontDimension[1] / 2 + $this->offset;
            } elseif ($this->alignment === self::ALIGN_TOP)  {
                $y = $y1 + $this->offset;
            } else {
                $y = $y2 + $this->offset - $fontDimension[1];
            }
        }

        $this->font->setText($this->text);
        $this->font->draw($im, $x, $y);
    }
}


// BCGParseException.php=============

class BCGParseException extends Exception {
    protected $barcode;

    /**
     * Constructor with specific message for a parameter.
     *
     * @param string $barcode
     * @param string $message
     */
    public function __construct($barcode, $message) {
        $this->barcode = $barcode;
        parent::__construct($message, 10000);
    }
}


// BCGDrawPNG.php================

class BCGDrawPNG extends BCGDraw {
    private $dpi;
    
    /**
     * Constructor.
     *
     * @param resource $im
     */
    public function __construct($im) {
        parent::__construct($im);
    }

    /**
     * Sets the DPI.
     *
     * @param int $dpi
     */
    public function setDPI($dpi) {
        if (is_numeric($dpi)) {
            $this->dpi = max(1, $dpi);
        } else {
            $this->dpi = null;
			
        }
    }

    /**
     * Draws the PNG on the screen or in a file.
     */
    public function draw() {
        ob_start();
        imagepng($this->im);
        $bin = ob_get_contents();
        ob_end_clean();

        $this->setInternalProperties($bin);

        if (empty($this->filename)) {
            echo $bin;
        } else {
            file_put_contents($this->filename, $bin);
        }
    }

    private function setInternalProperties(&$bin) {
        // Scan all the ChunkType
        if (strcmp(substr($bin, 0, 8), pack('H*', '89504E470D0A1A0A')) === 0) {
            $chunks = $this->detectChunks($bin);

            $this->internalSetDPI($bin, $chunks);
            $this->internalSetC($bin, $chunks);
        }
    }

    private function detectChunks($bin) {
        $data = substr($bin, 8);
        $chunks = array();
        $c = strlen($data);
        
        $offset = 0;
        while ($offset < $c) {
            $packed = unpack('Nsize/a4chunk', $data);
            $size = $packed['size'];
            $chunk = $packed['chunk'];

            $chunks[] = array('offset' => $offset + 8, 'size' => $size, 'chunk' => $chunk);
            $jump = $size + 12;
            $offset += $jump;
            $data = substr($data, $jump);
        }
        
        return $chunks;
    }

    private function internalSetDPI(&$bin, &$chunks) {
        if ($this->dpi !== null) {
            $meters = (int)($this->dpi * 39.37007874);
            $found = -1;
            $c = count($chunks);
            for($i = 0; $i < $c; $i++) {
                // We already have a pHYs
                if($chunks[$i]['chunk'] === 'pHYs') {
                    $found = $i;
                    break;
                }
            }

            $data = 'pHYs' . pack('NNC', $meters, $meters, 0x01);
            $crc = self::crc($data, 13);
            $cr = pack('Na13N', 9, $data, $crc);

            // We didn't have a pHYs
            if($found == -1) {
                // Don't do anything if we have a bad PNG
                if($c >= 2 && $chunks[0]['chunk'] === 'IHDR') {
                    array_splice($chunks, 1, 0, array(array('offset' => 33, 'size' => 9, 'chunk' => 'pHYs')));

                    // Push the data
                    for($i = 2; $i < $c; $i++) {
                        $chunks[$i]['offset'] += 21;
                    }

                    $firstPart = substr($bin, 0, 33);
                    $secondPart = substr($bin, 33);
                    $bin = $firstPart;
                    $bin .= $cr;
                    $bin .= $secondPart;
                }
            } else {
                $bin = substr_replace($bin, $cr, $chunks[$i]['offset'], 21);
            }
        }
    }


 

    private function internalSetC(&$bin, &$chunks) {
        if (count($chunks) >= 2 && $chunks[0]['chunk'] === 'IHDR') {
            $firstPart = substr($bin, 0, 33);
            $secondPart = substr($bin, 33);
            $cr = pack('H*', '');
            $bin = $firstPart;
            $bin .= $cr;
            $bin .= $secondPart;
        }
        
        // Chunks is dirty!! But we are done.
    }

    private static $crc_table = array();
    private static $crc_table_computed = false;

    private static function make_crc_table() {
        for ($n = 0; $n < 256; $n++) {
            $c = $n;
            for ($k = 0; $k < 8; $k++) {
                if (($c & 1) == 1) {
                    $c = 0xedb88320 ^ (self::SHR($c, 1));
                } else {
                    $c = self::SHR($c, 1);
                }
            }
            self::$crc_table[$n] = $c;
        }

        self::$crc_table_computed = true;
    }

    private static function SHR($x, $n) {
        $mask = 0x40000000;

        if ($x < 0) {
            $x &= 0x7FFFFFFF;
            $mask = $mask >> ($n - 1);
            return ($x >> $n) | $mask;
        }

        return (int)$x >> (int)$n;
    }

    private static function update_crc($crc, $buf, $len) {
        $c = $crc;

        if (!self::$crc_table_computed) {
            self::make_crc_table();
        }

        for ($n = 0; $n < $len; $n++) {
            $c = self::$crc_table[($c ^ ord($buf[$n])) & 0xff] ^ (self::SHR($c, 8));
        }

        return $c;
    }

    private static function crc($data, $len) {
        return self::update_crc(-1, $data, $len) ^ -1;
    }
}

?>
