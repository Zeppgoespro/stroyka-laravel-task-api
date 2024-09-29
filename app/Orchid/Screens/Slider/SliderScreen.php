<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Slider;

use App\Models\Slider;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class SliderScreen extends Screen
{
    /**
     * Query to fetch the list of sliders.
     */
    public function query(): iterable
    {
        return [
            'sliders' => Slider::paginate(),
        ];
    }

    /**
     * The name of the screen.
     */
    public function name(): ?string
    {
        return 'Slider Management';
    }

    /**
     * The screen's description.
     */
    public function description(): ?string
    {
        return 'Manage the slider images displayed on the main page';
    }

    /**
     * The screen's command bar with an "Add" button.
     */
    public function commandBar(): iterable
    {
        return [
            Link::make('Add Slider')
                ->icon('bs.plus-circle')
                ->route('platform.systems.sliders.create'),
        ];
    }

    /**
     * The layout for displaying the list of sliders.
     */
    public function layout(): iterable
    {
        return [
            Layout::table('sliders', [
                TD::make('id', 'ID'),

                TD::make('image_path', 'Image')
                    ->render(fn(Slider $slider) => "<img src='{$slider->image_path}' style='width: 150px;'/>"),

                TD::make('title', 'Title'),

                TD::make('description', 'Description'),

                TD::make('Actions')
                    ->render(fn(Slider $slider) => Link::make('Edit')
                        ->route('platform.systems.sliders.edit', $slider)),
            ]),
        ];
    }
}
