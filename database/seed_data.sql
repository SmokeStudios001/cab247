-- seed_data.sql
-- Default data for the taxi_booking database

-- Insert default admin user with a hashed password 'password123'
INSERT IGNORE INTO `admin_users` (`id`, `username`, `password`, `email`) VALUES
(1, 'admin', '$2y$10$wT.fB9R5h9T5.Yq1/4p0i.fR/L9z.o.v.M.kY.fR/L9z.o.v.M.kY', 'admin@example.com');

-- Insert default settings
INSERT IGNORE INTO `settings` (`setting_key`, `setting_value`, `description`) VALUES
('company_name', 'RideShare', 'The name of the company.'),
('currency', 'USD', 'The currency used for transactions.'),
('min_fare', '5.00', 'The minimum fare for a ride.'),
('max_wait_time', '300', 'Maximum wait time for a driver in seconds.'),
('search_radius', '5', 'Radius in kilometers to search for drivers');