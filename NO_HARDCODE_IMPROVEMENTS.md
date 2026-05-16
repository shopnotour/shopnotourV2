# No Hardcode Improvements - All Data from Sabre

## Summary
All hardcoded values have been removed. All data now comes from:
1. **Sabre API responses** (during addToCart)
2. **Stored booking data** (during PNR creation)
3. **Proper validation** with error handling (no silent failures)

## Changes Made

### 1. ✅ Passenger Name Validation
**Before:** Hardcoded fallbacks `'TEST'` and `'USER'`
**After:** 
- Validates names exist before proceeding
- Returns error if names are missing
- Logs errors for tracking

### 2. ✅ Gender Extraction
**Before:** Hardcoded default `'M'`
**After:**
- Extracts from passenger data with multiple format support
- Handles: 'M', 'MALE', 'F', 'FEMALE', etc.
- Only uses default if completely missing (with logging)

### 3. ✅ Phone Number Validation
**Before:** Hardcoded fallback `"0000000000"`
**After:**
- Validates phone exists and has at least 7 digits
- Returns error if invalid/missing
- Cleans phone number format

### 4. ✅ Carrier Code Extraction
**Before:** Hardcoded fallback `'XX'`
**After:**
- Extracts from route data (multiple field variations)
- Validates carrier code exists
- Returns error if missing
- Tries: `carrier_code`, `carrierCode`, `carrier.code`, `marketingCarrier.code`

### 5. ✅ Flight Number Extraction
**Before:** Hardcoded fallback `'0000'`
**After:**
- Extracts from route data (multiple field variations)
- Validates flight number exists
- Returns error if missing
- Tries: `flight_number`, `number`, `flightNumber`, `carrier.flightNumber`

### 6. ✅ Route IATA Codes
**Before:** Hardcoded fallback `'N/A'`
**After:**
- Extracts from Sabre response (multiple field variations)
- Validates departure/arrival codes exist
- Skips invalid segments (logs error)
- Tries: `departure_iata_code`, `departure.iataCode`, `departure.iata`

### 7. ✅ Route Dates
**Before:** Could have backwards dates
**After:**
- Extracts from Sabre response (multiple field variations)
- Validates dates exist
- Auto-fixes backwards dates
- Tries: `departure_at`, `departure.at`, `departureDateTime`

### 8. ✅ ResBookDesigCode (Booking Class)
**Before:** Hardcoded default `'Y'`
**After:**
- Extracts from route meta → passenger → booking (in order)
- Maps seat class names to Sabre codes
- Only uses 'Y' as last resort (with logging)
- Checks: route.meta.class, passenger.class, booking.seat_class

### 9. ✅ Aircraft Code
**Before:** Hardcoded fallback `'N/A'`
**After:**
- Extracts from Sabre response (optional field)
- Uses 'N/A' only for truly optional field
- Tries: `aircraft_code`, `aircraft.code`, `equipment.code`

### 10. ✅ Debug Statements Removed
**Before:** `dd()` and `echo print_r()` statements
**After:**
- Replaced with proper logging
- No execution-stopping debug code

## Data Extraction Flow

### During Add to Cart (Flight.php)
1. **Extract from Sabre response** with multiple field variations
2. **Validate critical fields** (carrier, flight number, dates, IATA codes)
3. **Skip invalid segments** (log error, continue)
4. **Store validated data** in BookingRoute table

### During PNR Creation (BookingController.php)
1. **Load stored routes** from database
2. **Validate all required data** exists
3. **Return error** if critical data missing (no silent failures)
4. **Extract from stored data** (no hardcoded values)
5. **Log all operations** for audit trail

## Validation Rules

### Critical Fields (Must Exist)
- ✅ Passenger first_name, last_name
- ✅ Passenger gender (with format validation)
- ✅ Booking/Passenger phone (min 7 digits)
- ✅ Route carrier_code
- ✅ Route flight_number
- ✅ Route departure_iata_code, arrival_iata_code
- ✅ Route departure_at, arrival_at

### Optional Fields (Can Use Fallback)
- ⚠️ Aircraft code → 'N/A' (truly optional)
- ⚠️ Duration → null (can be calculated)
- ⚠️ ResBookDesigCode → 'Y' (only if all sources fail, with logging)

## Error Handling

All validation failures now:
1. **Log error** with context
2. **Return false** (stop execution)
3. **No silent failures** (no hardcoded fallbacks for critical data)

## Logging

All operations logged:
- **Errors:** Missing critical data, validation failures
- **Warnings:** Using defaults, format corrections
- **Info:** Data mapping, successful operations

## Testing Checklist

- [x] Missing passenger name → Returns error
- [x] Missing phone → Returns error
- [x] Missing carrier code → Returns error
- [x] Missing flight number → Returns error
- [x] Missing route dates → Returns error
- [x] Missing IATA codes → Returns error
- [x] Invalid gender format → Uses default with warning
- [x] Missing booking class → Uses 'Y' with warning
- [x] All data from Sabre → Properly extracted and stored


