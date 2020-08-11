<?php

namespace Integrai\Core\Model\Observer;

class Events{
    const SAVE_CUSTOMER = 'SAVE_CUSTOMER';
    const CUSTOMER_BIRTHDAY = 'CUSTOMER_BIRTHDAY';
    const NEWSLETTER_SUBSCRIBER = 'NEWSLETTER_SUBSCRIBER';
    const ADD_PRODUCT_CART = 'ADD_PRODUCT_CART';
    const ABANDONED_CART = 'ABANDONED_CART';
    const NEW_ORDER = 'NEW_ORDER';
    const SAVE_ORDER = 'SAVE_ORDER';
    const CANCEL_ORDER = 'CANCEL_ORDER';
}