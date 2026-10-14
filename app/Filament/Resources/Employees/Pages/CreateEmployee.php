<?php

namespace App\Filament\Resources\Employees\Pages;

use App\Filament\Resources\Employees\EmployeeResource;
use App\Models\Employee;
use Filament\Resources\Pages\CreateRecord;

class CreateEmployee extends CreateRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function afterCreate(): void
    {
        /** @var Employee $employee */
        $employee = $this->getRecord();

        $employee->sendInvitation();
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Employé créé et invitation envoyée';
    }
}
