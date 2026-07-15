<?php

namespace Tests\Unit;

use App\Game\Commands\WaitCommand;
use App\Game\Services\CodeFeedbackService;
use App\Game\Services\CommandParserService;
use Tests\TestCase;

class WaitCommandParserTest extends TestCase
{
    public function test_wait_command_is_parsed_and_expanded(): void
    {
        $parser = new CommandParserService();

        $commands = $parser->parse('hero.wait(3)');

        $this->assertFalse($parser->hasErrors());
        $this->assertCount(3, $commands);
        $this->assertContainsOnlyInstancesOf(WaitCommand::class, $commands);
    }

    public function test_wait_command_is_accepted_by_feedback_service(): void
    {
        $feedback = (new CodeFeedbackService())->analyze('hero.wait()');

        $this->assertSame([], $feedback);
    }
}
