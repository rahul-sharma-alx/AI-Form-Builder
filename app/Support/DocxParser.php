<?php

namespace App\Support;

use PhpOffice\PhpWord\Element\Cell;
use PhpOffice\PhpWord\Element\CheckBox;
use PhpOffice\PhpWord\Element\ListItem;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\Element\Text;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\Element\Title;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Style\Paragraph;

class DocxParser
{
    public static function parse(string $path): array
    {
        $items = [];

        foreach (self::lines($path) as $line) {
            $text = trim($line['text']);
            if ($text === '') {
                continue;
            }

            if ($line['heading']) {
                $items[] = ['type' => 'heading', 'text' => $text];

                continue;
            }

            $option = $line['option'] ? $text : self::checkboxText($text);

            if ($option !== null && $option !== '') {
                $last = array_key_last($items);
                if ($last !== null && $items[$last]['type'] === 'question') {
                    $items[$last]['options'][] = $option;
                } else {
                    $items[] = ['type' => 'question', 'text' => $option, 'options' => []];
                }

                continue;
            }

            $items[] = ['type' => 'question', 'text' => $text, 'options' => []];
        }

        return $items;
    }

    protected static function lines(string $path): array
    {
        $phpWord = IOFactory::load($path);
        $lines = [];

        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                foreach (self::elementLines($element) as $line) {
                    $lines[] = $line;
                }
            }
        }

        return $lines;
    }

    protected static function elementLines(object $element): array
    {
        if ($element instanceof Title) {
            return [self::line((string) $element->getText(), heading: true)];
        }

        if ($element instanceof TextRun) {
            return [self::line(self::runsText($element), heading: self::isHeadingStyle($element->getParagraphStyle()))];
        }

        if ($element instanceof Text) {
            return [self::line((string) $element->getText())];
        }

        if ($element instanceof ListItem) {
            return [self::line((string) $element->getText())];
        }

        if ($element instanceof CheckBox) {
            return [self::line((string) $element->getText())];
        }

        if ($element instanceof Table) {
            return self::tableLines($element);
        }

        return [];
    }

    protected static function tableLines(Table $table): array
    {
        $lines = [];

        foreach ($table->getRows() as $row) {
            $cells = [];

            foreach ($row->getCells() as $cell) {
                $text = trim(self::cellText($cell));
                if ($text !== '') {
                    $cells[] = $text;
                }
            }

            if (count($cells) === 0) {
                continue;
            }

            $lines[] = self::line($cells[0]);

            foreach (array_slice($cells, 1) as $option) {
                $lines[] = self::line($option, option: true);
            }
        }

        return $lines;
    }

    protected static function cellText(Cell $cell): string
    {
        $text = '';

        foreach ($cell->getElements() as $element) {
            if ($element instanceof TextRun) {
                $text .= self::runsText($element).' ';
            } elseif ($element instanceof Text) {
                $text .= $element->getText().' ';
            }
        }

        return $text;
    }

    protected static function runsText(TextRun $run): string
    {
        $text = '';

        foreach ($run->getElements() as $child) {
            if ($child instanceof Text) {
                $text .= $child->getText();
            }
        }

        return $text;
    }

    protected static function line(string $text, bool $heading = false, bool $option = false): array
    {
        return ['text' => $text, 'heading' => $heading, 'option' => $option];
    }

    protected static function isHeadingStyle(mixed $style): bool
    {
        $name = $style instanceof Paragraph ? (string) $style->getStyleName() : (string) $style;

        return str_contains($name, 'Heading') || $name === 'Title';
    }

    protected static function checkboxText(string $text): ?string
    {
        if (preg_match('/^([\x{2610}\x{2611}\x{2612}\x{25A1}\x{2B1C}\x{2B1E}]|[\[(]\s?[xX ]\s?[\])])\s*(.*)$/u', $text, $m)) {
            return trim($m[2]) !== '' ? trim($m[2]) : null;
        }

        return null;
    }
}
