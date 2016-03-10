# Upgrade notes

[go back to Table of contents][back-to-index]

-----

This page contains all upgrade notes since the release of the stable 2.0.0 version.

## 2.1.0

- Feature: Forward on missing object during deletion. When a object is not found, previous an InvalidArgumentException
  was thrown. This has changed to an HttpNotFoundException. By default, this exception is now catched to forward the 
  user to the list with the notification that the object no longer exists.

[back-to-index]: README.md