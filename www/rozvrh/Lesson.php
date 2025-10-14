<?php

class Lesson
{
    private string $subject;
    private string $teacher;
    private string $room;

    public function __construct(string $subject, string $teacher, string $room)
    {
        $this->subject = $subject;
        $this->teacher = $teacher;
        $this->room = $room;
    }

    public function getHtml(): string
    {
        return "<div class='lesson'>
                    <h2>" . htmlspecialchars($this->subject) . "</h2>
                    <p>" . htmlspecialchars($this->teacher) . "</p>
                    <p>Room: " . htmlspecialchars($this->room) . "</p>
                </div>";
                // Zde AI vypomohla se syntaxí
    }
}