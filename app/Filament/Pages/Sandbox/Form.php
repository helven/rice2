<?php

namespace App\Filament\Pages\Sandbox;


use App\Filament\Pages\AbstractFilamentPage;
use Filament\Pages\Page;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Assets\Css;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Fieldset;

class Form extends AbstractFilamentPage 
{
    use InteractsWithForms;

    protected static ?string $navigationGroup = 'Sandbox';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Form Sandbox';
    protected static ?string $title = 'Form Sandbox';
    protected static ?string $slug = 'sandbox/form';
    protected static ?int $navigationSort = 3;
    
    protected static string $view = 'filament.pages.sandbox.form';

    // Form properties
    public $name;
    public $email;
    public $description;
    public $country;
    public $interests = [];
    public $gender;
    public $birthDate;
    public $newsletter = false;

    protected function getFormSchema(): array
    {
        return [
            // Main content in a two column layout
            Grid::make(2)->schema([
                // Left column with main form fields
                Group::make()->schema([
                    Card::make()->schema([
                        TextInput::make('name')
                            ->label('Full Name')
                            ->required()
                            ->extraAttributes([
                                'onkeyup' => 'searchName(event.target.value)',
                            ]),

                        TextInput::make('email')
                            ->label('Email Address')
                            ->email()
                            ->required(),
                            
                        Textarea::make('description')
                            ->label('About You')
                            ->rows(3),
                    ])->columns(1),

                    // Address information in a collapsible section
                    Section::make('Address Information')
                        ->collapsible()
                        ->schema([
                            Select::make('country')
                                ->label('Country')
                                ->options([
                                    'us' => 'United States',
                                    'uk' => 'United Kingdom',
                                    'ca' => 'Canada',
                                ]),

                            TextInput::make('city')
                                ->label('City'),

                            TextInput::make('postal_code')
                                ->label('Postal Code'),
                        ])->columns(2),
                ])->columnSpan(1),

                // Right column with additional fields
                Group::make()->schema([
                    // Personal preferences in a fieldset
                    Fieldset::make('Personal Preferences')
                        ->schema([
                            CheckboxList::make('interests')
                                ->label('Interests')
                                ->options([
                                    'sports' => 'Sports',
                                    'music' => 'Music',
                                    'tech' => 'Technology',
                                ])
                                ->columns(2),

                            Select::make('gender')
                                ->options([
                                    'm' => 'Male',
                                    'f' => 'Female',
                                    'o' => 'Other',
                                ]),

                            DatePicker::make('birthDate')
                                ->label('Birth Date'),
                        ]),

                    // Additional information card
                    Card::make()->schema([
                        Toggle::make('newsletter')
                            ->label('Subscribe to newsletter')
                            ->inline(false),

                        Placeholder::make('note')
                            ->label('Important Note')
                            ->content('All fields marked with * are required'),
                    ])->columns(1),
                ])->columnSpan(1),
            ]),
        ];
    }

    public function mount(): void
    {
        // Initialize form properties
        $this->form->fill([
            'interests' => [],
            'newsletter' => false,
        ]);
    }

    public function save()
    {
        $data = $this->form->getState();
        
        // Handle form submission
        dd($data); // For testing, replace with your logic
    }
}
