<?php

namespace Integrai\Core\Model\Observer;

class Events{
    const CREATE_CUSTOMER = 'CREATE_CUSTOMER';
    const UPDATE_CUSTOMER = 'UPDATE_CUSTOMER';
    const CUSTOMER_BIRTHDAY = 'CUSTOMER_BIRTHDAY';
    const CREATE_LEAD = 'CREATE_LEAD';
    const ADD_PRODUCT_CART = 'ADD_PRODUCT_CART';
    const ADD_PRODUCT_CART_ITEM = 'ADD_PRODUCT_CART_ITEM';
    const ABANDONED_CART = 'ABANDONED_CART';
    const ABANDONED_CART_ITEM = 'ABANDONED_CART_ITEM';
    const CREATE_ORDER = 'CREATE_ORDER';
    const UPDATE_ORDER = 'UPDATE_ORDER';
    const UPDATE_ORDER_ITEM = 'UPDATE_ORDER_ITEM';
    const QUOTE = 'QUOTE';
    const CREATE_PRODUCT = 'CREATE_PRODUCT';
    const UPDATE_PRODUCT = 'UPDATE_PRODUCT';
    const DELETE_PRODUCT = 'DELETE_PRODUCT';
    const BOLETO_URL = 'BOLETO_URL';
    const PIX = 'PIX';
}
