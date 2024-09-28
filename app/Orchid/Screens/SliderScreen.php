<?php

namespace App\Orchid\Screens;

use App\Models\Slider;
use Orchid\Screen\Screen;
use Orchid\Screen\Actions\Link;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\TD;

class SliderScreen extends Screen
{
    public function query(): iterable
    {
        return [
            'sliders' => Slider::paginate(),
        ];
    }

    public function name(): ?string
    {
        return 'Slider Management';
    }

    public function commandBar(): iterable
    {
        return [
            Link::make('Add New Slider')
                ->icon('plus')
                ->route('platform.slider.edit'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('sliders', [
                TD::make('image_path', 'Image')
                    ->render(function (Slider $slider) {
                        return "<img src='" . asset('storage/' . $slider->image_path) . "' alt='Slider image' width='100'>";
                    }),
                TD::make('title', 'Title'),
                TD::make('description', 'Description'),
                TD::make('created_at', 'Created At')
                    ->render(function (Slider $slider) {
                        return $slider->created_at->toDateTimeString();
                    }),
                TD::make('Actions')
                    ->render(function (Slider $slider) {
                        return Link::make('Edit')
                            ->route('platform.slider.edit', $slider)
                            ->icon('pencil');
                    }),
            ]),
        ];
    }
}
