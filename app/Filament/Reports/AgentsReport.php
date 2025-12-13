<?php

namespace App\Filament\Reports;

use EightyNine\Reports\Report;
use Filament\Schemas\Schema;
use EightyNine\Reports\Components\Text;


class AgentsReport extends Report
{
    public ?string $heading = "Report";

    // public ?string $subHeading = "A great report";

    public function header(Schema $schema): Schema
    {
        return $schema->components([
            Text::make('Agents Report')->title()->primary(),
        ]);
    }

    public function body(Schema $schema): Schema
    {
        return $schema->components([
            // Add main report content
        ]);
    }

    public function footer(Schema $schema): Schema
    {
        return $schema->components([
            // Add footer if needed
        ]);
    }

    public function filterForm(Schema $schema): Schema
    {
        return $schema->components([
            // Add filter form fields
        ]);
    }
}
