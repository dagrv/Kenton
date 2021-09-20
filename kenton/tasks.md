# Left To do
- [x] Offices approval status must be pending, approved, never rejected.
- [x] Store `Office` in DB Transaction
- [] Filter offices returned
- [x] Office Update
- [] Pagination offices
- [] Create office
- [] Show offices

#### Offices Endpoint
- [] List Offices
- [] Read Offices
- [] Create Office
- [] Add photos to offices endpoint
- [] Mark as `pending` when critical attributes are updated and notify the admin
- [] Notify admin on new office creation
- [x] Can only update their own offices
- [x] User must be authenticated & Email must been verified
- [x] Change user_id filter to visitor_id && host_id to user_id
- [x] Switch to using Custom Polymorphic Types
- [x] Order by distance but don't include the distance
- [x] Configure resources
- [x] Token authorization to allow `office.create`

#### Filtering
- [] User can only list their own reservation(s) on their office
- [] Allow filtering by office_id
- [] Allow filtering by user_id
- [] Allow filtering date range
- [] Allow filtering by status

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