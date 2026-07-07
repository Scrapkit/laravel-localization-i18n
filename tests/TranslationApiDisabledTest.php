<?php

it('does not register the translations api when disabled', function () {
    $this->getJson('/api/translations/it/common')->assertNotFound();
});
