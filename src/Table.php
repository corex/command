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
        foreach ($headers as $header) {
            $this->updateWidth($header, strlen($header));
            if (!in_array($header, $this->headers)) {
                $this->headers[] = $header;
            }
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
            foreach ($row as $column => $value) {
                $this->updateWidth($column, strlen($value));
                if (!in_array($column, $this->columns)) {
                    $this->columns[] = $column;
                }
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
            foreach ($this->columns as $index => $column) {
                $title = $column;
                if (isset($this->headers[$index])) {
                    $title = $this->headers[$index];
                }
                $line[] = $this->renderCell($column, $title, ' ', 'info');
                $line[] = $this->charVertical;
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
            foreach ($this->columns as $column) {
                $output[] = $this->renderCell($column, $this->charHorizontal, $this->charHorizontal);
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
        foreach ($row as $column => $value) {
            $output[] = $this->renderCell($column, $value, ' ');
            $output[] = $this->charVertical;
        }
        return implode('', $output);
    }

    /**
     * Render cell.
     *
     * @param string $column
     * @param string $value
     * @param string $filler
     * @param string $style Default ''.
     * @return string
     */
    private function renderCell($column, $value, $filler, $style = '')
    {
        $output = [];
        $width = $this->getWidth($column);
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
     * @param string $column
     * @return integer
     */
    private function getWidth($column)
    {
        if (isset($this->widths[$column])) {
            return intval($this->widths[$column]);
        }
        return 0;
    }

    /**
     * Update width.
     *
     * @param string $column
     * @param integer $width
     */
    private function updateWidth($column, $width)
    {
        if ($width > $this->getWidth($column)) {
            $this->widths[$column] = $width;
        }
    }
}