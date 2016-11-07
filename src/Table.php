<?php

namespace CoRex\Command;

class Table
{
    private $headers;
    private $columns;
    private $widths;
    private $rows;

    private $charCross = '+';
    private $charHorizontal = '-';
    private $charVertical = '|';

    /**
     * Table constructor.
     */
    public function __construct()
    {
        $this->headers = [];
        $this->columns = [];
        $this->widths = [];
        $this->rows = [];
    }

    /**
     * Set headers.
     *
     * @param array $headers
     */
    public function setHeaders(array $headers)
    {
        if (!is_array($headers)) {
            return;
        }
        $columnNumber = 0;
        foreach ($headers as $header) {
            $this->updateWidth($columnNumber, strlen($header));
            if (!in_array($header, $this->headers)) {
                $this->headers[] = $header;
            }
            $columnNumber++;
        }
    }

    /**
     * Set rows.
     *
     * @param array $rows
     */
    public function setRows(array $rows)
    {
        if (!is_array($rows)) {
            return;
        }
        foreach ($rows as $row) {
            $columnNumber = 0;
            foreach ($row as $column => $value) {
                $this->updateWidth($columnNumber, strlen($column));
                $this->updateWidth($columnNumber, strlen($value));
                if (!in_array($column, $this->columns)) {
                    $this->columns[] = $column;
                }
                $columnNumber++;
            }
            $this->rows[] = $row;
        }
    }

    /**
     * Render table.
     *
     * @return string
     */
    public function render()
    {
        $output = [];

        // Top.
        $output[] = $this->renderLine();

        // Headers.
        if (count($this->columns) > 0) {
            $line = [];
            $line[] = $this->charVertical;
            $columnNumber = 0;
            foreach ($this->columns as $index => $column) {
                $title = $column;
                if (isset($this->headers[$index])) {
                    $title = $this->headers[$index];
                }
                $line[] = $this->renderCell($columnNumber, $title, ' ', 'info');
                $line[] = $this->charVertical;
                $columnNumber++;
            }
            $output[] = implode('', $line);
        }

        // Middle.
        $output[] = $this->renderLine();

        // Rows.
        if (count($this->rows) > 0) {
            foreach ($this->rows as $row) {
                $output[] = $this->renderRow($row);
            }
        }

        // Footer
        $output[] = $this->renderLine();

        return implode("\n", $output);
    }

    /**
     * Render line.
     *
     * @return string
     */
    private function renderLine()
    {
        $output = [];
        $output[] = $this->charCross;
        if (count($this->columns) > 0) {
            for ($columnNumber = 0; $columnNumber < count($this->columns); $columnNumber++) {
                $output[] = $this->renderCell($columnNumber, $this->charHorizontal, $this->charHorizontal);
                $output[] = $this->charCross;
            }
        }
        return implode('', $output);
    }

    /**
     * Render row.
     *
     * @param array $row
     * @return string
     */
    private function renderRow(array $row)
    {
        $output = [];
        $output[] = $this->charVertical;
        $columnNumber = 0;
        foreach ($row as $column => $value) {
            $output[] = $this->renderCell($columnNumber, $value, ' ');
            $output[] = $this->charVertical;
            $columnNumber++;
        }
        return implode('', $output);
    }

    /**
     * Render cell.
     *
     * @param integer $columnNumber
     * @param string $value
     * @param string $filler
     * @param string $style Default ''.
     * @return string
     */
    private function renderCell($columnNumber, $value, $filler, $style = '')
    {
        $output = [];
        $width = $this->getWidth($columnNumber);
        $output[] = $filler;
        while (strlen($value) < $width) {
            $value .= $filler;
        }
        $output[] = Style::applyStyle($value, $style);
        $output[] = $filler;
        return implode('', $output);
    }

    /**
     * Get width of column.
     *
     * @param integer $columnNumber
     * @return integer
     */
    private function getWidth($columnNumber)
    {
        if (isset($this->widths[$columnNumber])) {
            return $this->widths[$columnNumber];
        }
        return 0;
    }

    /**
     * Update width.
     *
     * @param integer $columnNumber
     * @param integer $width
     */
    private function updateWidth($columnNumber, $width)
    {
        if ($width > $this->getWidth($columnNumber)) {
            $this->widths[$columnNumber] = $width;
        }
    }
}