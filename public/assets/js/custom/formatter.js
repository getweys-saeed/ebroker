function imageFormatter( value, row ) {


    var svg_clr_setting = row.svg_clr;

    if ( svg_clr_setting != null && svg_clr_setting == 1 ) {
        var imageUrl = value;

        if ( value ) {

            if ( imageUrl.split( '.' ).pop() === 'svg' ) {

                return '<embed class="svg-img" src="' + value + '">';
            } else {
                console.log( value );
                return '<a class="image-popup-no-margins" href="' + value + '"><img class="rounded avatar-md shadow img-fluid" alt="" src="' + value + '" width="40"></a>';
            }
        }
    } else {
        return ( value !== '' ) ? '<a class="image-popup-no-margins" href="' + value + '"><img class="rounded avatar-md shadow " alt="" src="' + value + '" width="40"></a>' : '';
    }


}

function sub_category( value, row ) {
    return '<a href="get_sub_categories/' + row.id + '"> <div class="category_count">' + value + ' Sub Categories</div></a>';
}

function custom_fields( value, row ) {

    var rootUrl = window.location.protocol + '//' + window.location.host;
    return '<a href="' + rootUrl + '/category_custom_fields/' + row.id + '"> <div class="category_count">' + value + ' Custom Fields</div></a>';

}


function premium_status_switch( value, row ) {

    status = value == "1" ? "checked" : "";
    return '<div class="form-check form-switch" style="padding-left: 5.2rem;"> <input class = "form-check-input switch1"id = "' + row.id +
        '"onclick = "chk(this);" data-url="updateaccessability" type = "checkbox" role = "switch"' + status + ' value = ' + value + ' ></div>';
}


function badge( value, row ) {
    if ( value == "review" ) {
        badgClass = 'primary';
        badgeText = 'Under Review';
    }
    if ( value == "approve" ) {
        badgClass = 'success';
        badgeText = 'Approved';
    }
    if ( value == "reject" ) {
        badgClass = 'danger';
        badgeText = 'Rejected';
    }
    return '<span class="badge rounded-pill bg-' + badgClass +
        '">' + badgeText + '</span>';
}





function propertyTypeFormatter(value,row) {
    console.log(row.property_type_raw);

    if ( row.property_type_raw == 0 ) {
        return '<div class="sell_type btn btn-sm btn-secondary fw-bold">Commercial</div>';
    } else if ( row.property_type_raw == 1 ) {
        return '<div class="rent_type btn btn-sm  fw-bold" style="background-color:#f75454">Residential</div>';
    } else if ( row.property_type_raw == 2 ) {
        return '<div class="sold_type btn btn-sm btn-success fw-bold">Sold</div>';
    } else if ( row.property_type_raw == 3 ) {
        return '<div class="rented_type btn btn-sm btn-primary fw-bold">Rented</div>';
    }
}



function status_badge( value, row ) {
    if ( value == '0' ) {
        badgClass = 'danger';
        badgeText = 'OFF';
    } else {
        badgClass = 'success';
        badgeText = 'ON';
    }
    return '<span class="badge rounded-pill bg-' + badgClass +
        '">' + badgeText + '</span>';
}

function user_status_badge( value, row ) {
    if ( value == '0' ) {
        badgClass = 'danger';
        badgeText = 'Inacive';
    } else {
        badgClass = 'success';
        badgeText = 'Active';
    }
    return '<span class="badge rounded-pill bg-' + badgClass +
        '">' + badgeText + '</span>';
}

function style_app( value, row ) {
    return '<a class="image-popup-no-margins" href="images/app_styles/' + value + '.png"><img src="images/app_styles/' + value + '.png" alt="style_4"  height="60" width="60" class="rounded avatar-md shadow img-fluid"></a>';
}

function filters( value ) {


    if ( value == "most_liked" ) {

        filter = "Most Liked";
    } else if ( value == "price_criteria" ) {
        filter = "Price Criteria";
    } else if ( value == "category_criteria" ) {
        filter = "Category Criteria";
    } else if ( value == "most_viewed" ) {
        filter = "Most Viewed";
    }
    return filter;
}

function adminFile( value, row ) {
    return "<a href='languages/" + row.code + ".json ' )+' > View File < /a>";

}

function appFile( value, row ) {
    return "<a href='lang/" + row.code + ".json ' )+' > View File < /a>";
}

function enableDisableFeaturedPropertiesFormatter(value,row){
    let status = row.status == '1' ? 'checked' : '';
    return `<div class="form-check form-switch center" style="margin-top: 10%;padding-left: 5.2rem;">
                <input class="form-check-input switch1" id="${row.id}"  onclick="chk(this);" type="checkbox" role="switch" ${status} '>
            </div>`
}

function featuredPropertiesDataFormatter(value,row){
    return `<div class="featured_property">
                <div class="image-container">
                    <img src="${row.title_image}" alt="Image">
                    <div class="featured-property-type"> ${row.type} </div>
                </div>
            <div>
            <div class="d-flex">
                <img src="${row.category.image}" alt="Image" height="24px" width="24px">
                <div class="category"> ${row.category.category} </div>
            </div>
            <div class="title"> ${row.title} </div>
            <div class="price"> ${row.price} </div>
            <div class="city">
                <i class="bi bi-geo-alt"></i>
                ${row.city}
            </div>`;
}

function enableDisableSwitchFormatter( value, row ) {
    let status = (value == "1") ? "checked" : "";
    return `<div class="form-check form-switch" style="padding-left: 5.2rem;">
                <input class = "form-check-input switch1"id = "${row.id}" onclick = "chk(this);" data-url="${row.edit_status_url}" type="checkbox" role="switch" ${status} value="${value}">
            </div>`;
}


function enableDisableFeatureSwitchFormatter(value, row) {
    let status = (value == 1) ? "checked" : "";
    console.log(row.id);

    return `<div class="form-check form-switch" style="padding-left: 5.2rem;">
        <input class="form-check-input switch1"
               id="${row.id}"
               name="featured_property"
               onclick="chkpro(this, ${row.id});"
               data-url="updatepropertyfeaturestatus"
               type="checkbox"
               role="switch"
               ${status}
               value="${value}">
    </div>`;
}


function chkpro(element, propertyId) {
    let isFeatured = element.checked ? 1 : 0; // Set featured status based on checkbox state

    $.ajax({
        url: $(element).data('url'),
        type: 'POST',
        data: {
            '_token': $('meta[name="csrf-token"]').attr('content'),
            "id": parseInt(element.id),
            "featured_property": isFeatured,
        },

        success: function(response) {
            Toastify({
                text: response.message,
                duration: 6000,
                close: true,
                backgroundColor: "linear-gradient(to right, #00b09b, #96c93d)"
            }).showToast();
            $('#table_list').bootstrapTable('refresh');
        },
        error: function(xhr) {
            console.error(xhr.responseText); // log error response
        }
    });
}
