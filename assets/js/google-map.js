function cities() {
    var options = {
        types: ['(postal_code)']
    };
    var location = document.getElementById('address');
    autocomplete = new google.maps.places.Autocomplete(location)

    // autocomplete = new google.maps.places.Autocomplete(
    //     document.getElementById('address'),
    //     { types: ['geocode'] } // Restrict to address results
    // );

    // Listen for place selection
    autocomplete.addListener('place_changed', handlePlaceSelect);
}

function handlePlaceSelect() {
    const place = autocomplete.getPlace();

    // Extract city and state
    let post_code = '';
    let city = '';
    let state = '';
    let country = '';

    if (place.address_components) {
        place.address_components.forEach(component => {
            console.log(component);
            
            const types = component.types;

            if (types.includes('postal_code')) {
                post_code = component.long_name; // City
            }

            if (types.includes('postal_town')) {
                city = component.long_name; // City
            }

            if (types.includes('administrative_area_level_1')) {
                state = component.long_name; // State
            }

            if (types.includes('country')) {
                country = component.long_name; // Country
            }
        });
    }

    document.getElementById('city').value = city;
    document.getElementById('state').value = state;
    document.getElementById('post_code').value = post_code;
    document.getElementById('country').value = country;
}