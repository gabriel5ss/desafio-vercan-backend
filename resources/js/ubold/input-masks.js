$(function () {
    $('[data-toggle="input-mask"]').each(function () {
        const $input = $(this);
        const options = {};
        const reverse = $input.data('reverse');

        if (reverse !== undefined) {
            options.reverse = reverse;
        }

        $input.mask($input.data('maskFormat'), options);
    });
});
