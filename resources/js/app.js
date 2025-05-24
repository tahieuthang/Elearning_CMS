import './bootstrap';
import * as FilePond from 'filepond';
import $ from 'jquery';
import { initializeFormValidation } from './validation';
// require('./bootstrap');

$(document).ready(function() {
    // Quy tắc và thông báo cho form 1
    const rulesForm1 = {
        username: {
            required: true,
            minlength: 3
        },
        email: {
            required: true,
            email: true
        }
    };

    const messagesForm1 = {
        username: {
            required: "Vui lòng nhập tên đăng nhập",
            minlength: "Tên đăng nhập phải ít nhất 3 ký tự"
        },
        email: {
            required: "Vui lòng nhập địa chỉ email",
            email: "Địa chỉ email không hợp lệ"
        }
    };

    initializeFormValidation("#myForm1", rulesForm1, messagesForm1);

    // Quy tắc và thông báo cho form 2
    const rulesForm2 = {
        password: {
            required: true,
            minlength: 6
        },
        confirm_password: {
            required: true,
            equalTo: "#password"
        }
    };

    const messagesForm2 = {
        password: {
            required: "Vui lòng nhập mật khẩu",
            minlength: "Mật khẩu phải ít nhất 6 ký tự"
        },
        confirm_password: {
            required: "Vui lòng xác nhận mật khẩu",
            equalTo: "Mật khẩu xác nhận không khớp"
        }
    };

    initializeFormValidation("#myForm2", rulesForm2, messagesForm2);
});