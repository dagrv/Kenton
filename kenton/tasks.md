# Kenton

### Left To do


_11 Oct 2021_


- [] Send an email to user and to the host when a reservation is made
- [] Send an email to user and to the host when it's the start day
- [] Generate WiFi Password for new reservations (unique & encrypted)

Done:
- [x] Restrict allow reservation for the same day
- [x] Read request input from validator output (`->validate()`)
- [x] Reserving an hidden or pending office should be forbidden
-----------------------------------------------------------------------------------------

_5 Oct 2021_ & _6 Oct 2021_
Done:
- [x] Cannot make reservation on their own property
- [x] Convert filtering reservations by date to Eloquent Scopes
- [x] Include reservations that started before range and ended after
- [x] Must be authenticated & email verified to make a reservation
- [x] No other reservation should conflict with the same date range
-----------------------------------------------------------------------------------------
_30 Sept 2021_  
Done:
- [x] Allow filtering by office_id
- [x] Allow filtering by user_id
- [x] Allow filtering date range
- [x] Allow filtering by status
- [x] User can only list their own reservation(s)
- [x] Switch to Sanctum Guard
- [x] Update Laravel to use assertNotSoftDeleted & assertModelMissing
- [x] Update Laravel to use LazilyRefreshDatabase (in tests)
- [x] Paginate
-----------------------------------------------------------------------------------------
_28 Sept 2021_  
Done:
- [x] Delete all images when deleting an office
- [x] Default disk should store public images, make switching process easier (in the future)
- [x] Use keyed implicit binding
-----------------------------------------------------------------------------------------
_23 Sept 2021_  
Done:
- [x] Only one photo is required and can be marked as `featured photo`
- [x] Attach photo to an office and make it possible to be a cover
- [x] Find an admin by adding attribute `is_admin` to users table
- [x] Return hidden and non-approved offices when filtering by `user_id` + User informations (to be easily tracke user and listing
- [x] Delete a photo

DONE PREVIOUSLY:
- [x] Offices approval status must be pending, approved, never rejected.
- [x] Store `Office` in DB Transaction
- [x] Office Update
- [x] Notify admin on new office creation
- [x] Mark as `pending` when critical attributes are updated and notify the admin
- [x] Can only update their own offices
- [x] User must be authenticated & Email must been verified
- [x] Change user_id filter to visitor_id && host_id to user_id
- [x] Switch to using Custom Polymorphic Types
- [x] Order by distance but don't include the distance
- [x] Configure resources
- [x] Token authorization to allow `office.create`
- [x] Must be auth' & email verified
- [x] Token must allow office.delete
- [x] Can only delete their own offices
- [x] Cant delete an office with an reservation on it
- [x] Prepare Migrations
- [x] Seed Initial Tags
- [x] Prepare Models
- [x] Prepare Factories
- [x] Prepare Resources
- [x] Tags for:
	- [x] Routes
	- [x] Controller
	- [x] Tests
- [x] Show only approved & visible records
- [x] Filter by hosts
- [x] Filter by users
- [x] Add tags, images, & user
- [x] Show previous reservations
- [x] Pagination
- [x] Sort by distance (if location provided)
-----------------------------------------------------------------------------------------

#### Offices Endpoint
- [] List Offices
- [] Read Offices
- [] Create Office
- [] Add photos to offices endpoint

#### Delete
- [] Cannot delete an office that has a reservation
- [] Cannot delete an office if it isn't their own
- [] User must be authenticated & email verified to delete 
- [] Validation
- [] Attaching photos to a specific office
- [] Choose a photo to be the feautured photo of the office
- [] Use locks to make the process atomic 

#### Reminders
- [] User & host will get an email when a reservation is confirmed
- [] User & host will get an email on reservation day (reminder)

#### Cancellation
- [] User must be authenticated & email should be verified to cancel an reservation
- [] User can only cancel their own reservation(s)
- [] User can only cancel an active reservation that has a start_date in the future. 
- [] Refund ?

*Dedicated to my dear friend Adam Kenton (RIP 1990-2019)*