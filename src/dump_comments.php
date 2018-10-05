<?php
declare(strict_types = 1);

/**
 * This script will dump only the code comments of a PHP file,
 * replacing everything else with blank space/blank lines,
 * and preserving the lines and columns of those comments.
 *
 * This can be used with tools such as codespell.
 */
class CommentDumper
{
    public static function extract_only_comments(string $contents) : string
    {
        $tokens = token_get_all($contents);

        $comments_only = '';
        $lineno = 1;

        foreach ($tokens as $token) {
            if (!is_array($token)) {
                continue;
            }
            if (!in_array($token[0], [T_COMMENT, T_DOC_COMMENT], true)) {
                continue;
            }
            $comment = $token[1];
            $expected_lineno = $token[2];
            if ($lineno < $expected_lineno) {
                $comments_only .= str_repeat("\n", $expected_lineno - $lineno);
                $lineno = $expected_lineno;
            }
            $comments_only .= $comment;
            $lineno += substr_count($comment, "\n");
        }
        // Terminate with newlines
        if ($comments_only !== '' && substr($comments_only, -1) !== "\n") {
            $comments_only .= "\n";
        }
        return $comments_only;
    }
    public static function main()
    {
        global $argv;
        if (count($argv) !== 2) {
            fwrite(
                STDERR,
                <<<EOT
Usage: $argv[0] file.php

Dumps the just comments of a PHP file with line numbers preserved.

EOT
            );
            exit(1);
        }
        $file = $argv[1];
        if (!file_exists($file)) {
            fwrite(STDERR, "Could not find file '$file'\n");
            exit(1);
        }
        if (!is_file($file)) {
            fwrite(STDERR, "Path '$file' is not a file\n");
            exit(1);
        }
        $contents = file_get_contents($file);
        $new_contents = self::extract_only_comments($contents);
        echo $new_contents;
    }
}
CommentDumper::main();
