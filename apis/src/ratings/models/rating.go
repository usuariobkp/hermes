package models

import (
	"time"

	"github.com/jinzhu/gorm"
)

type Rating struct {
	ID              uint   `gorm:"primary_key;AUTO_INCREMENT"`
	Rating          uint8  `gorm:"not null"`
	Description     string `gorm:"size:30"`
	AppVersion      string `gorm:"size:15"`
	PlatformVersion string `gorm:"size:15"`
	BrowserVersion  string `gorm:"size:15"`
	HasMessage      bool   `gorm:"not null;DEFAULT:false"`
	AppID           uint   `gorm:"not null"`
	Range           Range
	RangeID         uint `gorm:"not null"`
	AppUser         AppUser
	AppUserID       uint `gorm:"column:appuser_id"`
	Platform        Platform
	PlatformID      uint
	Device          Device
	DeviceID        uint
	Browser         Browser
	BrowserID       uint

	CreatedAt time.Time `gorm:"not null"`
}

// CreateRating creates a new rating
func CreateRating(rating *Rating, db *gorm.DB) *DB {
	return db.Create(rating)
}
